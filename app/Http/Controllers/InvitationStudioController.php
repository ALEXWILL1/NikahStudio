<?php

namespace App\Http\Controllers;

use App\Models\{Invitation, MediaAsset, Plan, Subscription, Template, User};
use App\Support\InvitationDesign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvitationStudioController extends Controller
{
    private function features(User $user): array
    {
        $sub = Subscription::where('user_id', $user->id)->where('status', 'active')
            ->where('starts_at', '<=', now())->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->whereHas('plan', fn ($q) => $q->where('is_active', true))->latest('starts_at')->first();
        $plan = $sub?->plan ?? Plan::where('slug', 'free')->first();
        return ($plan?->features ?? collect())->mapWithKeys(fn ($f) => [$f->feature_key => $f->feature_value['value']])->all()
            + ['max_invitations' => 1, 'max_gallery_images' => 5, 'premium_templates' => false, 'watermark' => true, 'rsvp_enabled' => true];
    }

    public function index(Request $request)
    {
        $features = $this->features($request->user());
        $list = Invitation::where('user_id', $request->user()->id)->withCount('rsvps')->latest('updated_at')->get();
        return Inertia::render('studio/home', [
            'screen' => match ($request->route()->getName()) {
                'studio.projects' => 'projects', 'studio.catalog' => 'templates', 'studio.new' => 'create',
                default => 'home',
            },
            'owner' => $request->user()->name,
            'limit' => $features['max_invitations'],
            'templates' => Template::where('is_active', true)->orderBy('id')->get()->map(fn ($t) => [
                'id' => $t->id, 'name' => $t->name, 'tier' => $t->tier, 'allowed' => $t->tier === 'free' || $features['premium_templates'],
                'category' => $t->category, 'samples' => InvitationDesign::make($t, 'Alya', 'Raka'),
            ]),
            'invitations' => $list->map(fn ($i) => [
                'id' => $i->id, 'title' => $i->title, 'published' => isset($i->settings['studio_live']),
                'url' => route('studio.guest', $i->slug), 'rsvps' => $i->rsvps_count,
                'cover' => $i->settings['studio_draft'][0] ?? null,
            ]),
        ]);
    }

    public function create(Request $request)
    {
        $data = $request->validate(['template_id' => 'nullable|integer', 'first' => 'required|string|max:65', 'second' => 'required|string|max:65']);
        $invitation = DB::transaction(function () use ($request, $data) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $features = $this->features($user);
            abort_if(Invitation::where('user_id', $user->id)->count() >= $features['max_invitations'], 422, 'Jumlah undangan sudah mencapai batas paket.');
            $template = empty($data['template_id']) ? null : Template::where('is_active', true)->findOrFail($data['template_id']);
            abort_if($template && $template->tier !== 'free' && ! $features['premium_templates'], 403, 'Template ini memerlukan paket Premium.');
            $base = $template ?? Template::firstOrCreate(['slug' => 'studio-blank'], [
                'name' => 'Kanvas kosong', 'view_key' => 'studio-blank', 'is_active' => false,
            ]);
            $title = $data['first'].' & '.$data['second'];
            return Invitation::create([
                'user_id' => $user->id, 'template_id' => $base->id, 'title' => $title,
                'partner_1_name' => $data['first'], 'partner_2_name' => $data['second'],
                'slug' => Str::substr(Str::slug($title) ?: 'undangan', 0, 110).'-'.Str::lower(Str::random(10)),
                'settings' => ['studio_draft' => InvitationDesign::make($template, $data['first'], $data['second']), 'studio_revision' => 0],
            ]);
        });
        return response()->json(['url' => route('studio.edit', $invitation)]);
    }

    private function owned(Request $request, $id, bool $lock = false): Invitation
    {
        $q = Invitation::where('user_id', $request->user()->id);
        if ($lock) {
            $q->lockForUpdate();
        }
        return $q->findOrFail($id);
    }

    public function edit(Request $request, $invitation)
    {
        $i = $this->owned($request, $invitation);
        return Inertia::render('studio/editor', [
            'id' => $i->id, 'title' => $i->title, 'sections' => $i->settings['studio_draft'] ?? InvitationDesign::make($i->template, $i->partner_1_name, $i->partner_2_name),
            'revision' => $i->settings['studio_revision'] ?? 0, 'published' => isset($i->settings['studio_live']),
            'url' => route('studio.guest', $i->slug), 'photos' => $i->mediaAssets()->where('file_type', 'image')->pluck('media_assets.id'),
        ]);
    }

    private function photoIds(array $sections): array
    {
        return collect($sections)->flatMap(fn ($s) => $s['items'])->where('type', 'photo')->pluck('value')->unique()->values()->all();
    }

    public function save(Request $request, $invitation)
    {
        $item = 'sections.*.items.*.';
        $hex = ['required','regex:/^#[0-9a-fA-F]{6}$/'];
        $data = $request->validate([
            'revision' => 'required|integer|min:0', 'title' => 'required|string|max:150',
            'sections' => 'required|array|list|min:1|max:24', 'sections.*' => 'array:id,label,kind,bg,items,backdrop,treatment',
            'sections.*.id' => 'required|uuid|distinct', 'sections.*.kind' => 'required|in:cover,couple,event,gallery,rsvp,custom',
            'sections.*.backdrop' => 'sometimes|nullable|in:mawar,rimba,toba,beludru,hortensia,senja,zaitun,lilin,cincin',
            'sections.*.treatment' => 'sometimes|in:full,arch,band,oval,faded',
            'sections.*.label' => 'required|string|max:80', 'sections.*.bg' => $hex, 'sections.*.items' => 'present|array|list|max:60',
            'sections.*.items.*' => 'array:id,type,value,x,y,w,size,color,font,angle,frame',
            $item.'id' => 'required|uuid|distinct', $item.'type' => 'required|in:text,photo,shape,illustration',
            $item.'value' => 'present|nullable|string|max:1500', $item.'font' => 'required|in:Georgia,Garamond,Arial,Verdana,monospace,cursive',
            $item.'x' => 'required|numeric|between:0,99', $item.'y' => 'required|numeric|between:0,99',
            $item.'w' => 'required|numeric|between:4,100', $item.'size' => 'required|numeric|between:2,20',
            $item.'angle' => 'required|numeric|between:-180,180', $item.'color' => $hex, $item.'frame' => 'required|in:soft,arch,round,square',
        ]);
        foreach ($data['sections'] as $section) {
            foreach ($section['items'] as $element) {
                abort_if($element['type'] === 'illustration' && ! in_array($element['value'], [
                    'ivory','olive','scroll','hydrangea','house','palm','peony','wreath','rings','jasmine','orchid','arch',
                ], true), 422, 'Ilustrasi tidak tersedia.');
                abort_if($element['type'] === 'shape' && ! in_array($element['value'], [
                    'branch', 'tree', 'flower', 'diamond', 'circle', 'square', 'line',
                ], true), 422, 'Pilih ornamen yang tersedia.');
            }
        }

        return DB::transaction(function () use ($request, $invitation, $data) {
            $i = $this->owned($request, $invitation, true);
            $s = $i->settings ?? [];
            abort_if(($s['studio_revision'] ?? 0) !== (int)$data['revision'], 409, 'Undangan berubah di tab lain. Muat ulang sebelum melanjutkan.');
            $ids = $this->photoIds($data['sections']);
            foreach ($ids as $id) {
                abort_unless(is_string($id) && preg_match('/^[1-9][0-9]{0,17}$/', $id), 422, 'ID foto tidak valid.');
            }
            abort_if($ids && $i->mediaAssets()->where('file_type', 'image')->whereIn('media_assets.id', $ids)->distinct()->count('media_assets.id') !== count($ids), 422, 'Foto tidak tersedia pada undangan ini.');
            $s['studio_draft'] = $data['sections'];
            $s['studio_revision'] = (int)$data['revision'] + 1;
            $i->update(['settings' => $s, 'title' => $data['title']]);
            return response()->json(['revision' => $s['studio_revision']]);
        });
    }

    public function upload(Request $request, $invitation)
    {
        $this->owned($request, $invitation);
        $request->validate(['photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192|dimensions:max_width=10000,max_height=10000']);
        $f = $request->file('photo');
        $path = null;
        try {
            return DB::transaction(function () use ($request, $invitation, $f, &$path) {
                $i = $this->owned($request, $invitation, true);
                abort_if($i->mediaAssets()->where('file_type', 'image')->count() >= $this->features($request->user())['max_gallery_images'], 422, 'Kuota foto paket ini sudah penuh.');
                $path = $f->store('invitation-studio/'.$i->id, 'local');
                abort_unless($path, 500, 'Foto gagal disimpan.');
                $asset = MediaAsset::create(['user_id' => $i->user_id, 'disk' => 'local', 'path' => $path,
                    'file_type' => 'image', 'mime_type' => $f->getMimeType(), 'size_bytes' => $f->getSize()]);
                $i->mediaAssets()->attach($asset->id, ['role' => 'studio', 'created_at' => now(), 'updated_at' => now()]);
                return response()->json(['id' => $asset->id]);
            });
        } catch (\Throwable $error) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $error;
        }
    }

    public function publish(Request $request, $invitation)
    {
        $request->validate(['revision' => 'required|integer|min:0']);
        return DB::transaction(function () use ($request, $invitation) {
            $i = $this->owned($request, $invitation, true);
            $s = $i->settings ?? [];
            abort_if(($s['studio_revision'] ?? 0) !== (int)$request->input('revision'), 409, 'Versi berubah. Muat ulang editor.');
            $draft = $s['studio_draft'] ?? [];
            abort_unless(collect($draft)->contains(fn ($section) => count($section['items']) > 0), 422, 'Tambahkan isi undangan sebelum menerbitkan.');
            $features = $this->features($request->user());
            $s['studio_live'] = ['sections' => $draft, 'title' => $i->title, 'watermark' => $features['watermark'], 'rsvp' => $features['rsvp_enabled']];
            $i->update(['settings' => $s, 'status' => 'published', 'published_at' => now()]);
            return response()->json(['url' => route('studio.guest', $i->slug)]);
        });
    }

    private function live($slug): Invitation
    {
        $i = Invitation::where('slug', $slug)->where('status', 'published')->firstOrFail();
        abort_unless(isset($i->settings['studio_live']), 404);
        return $i;
    }

    public function guest($slug)
    {
        $i = $this->live($slug);
        return Inertia::render('studio/guest', $i->settings['studio_live'] + ['id' => $i->id, 'slug' => $slug]);
    }

    public function photo(Request $request, Invitation $invitation, $asset)
    {
        $live = $invitation->status === 'published' ? ($invitation->settings['studio_live']['sections'] ?? []) : [];
        abort_unless($request->user()?->id === $invitation->user_id || in_array((string)$asset, $this->photoIds($live), true), 404);
        $file = $invitation->mediaAssets()->where('file_type', 'image')->where('media_assets.id', $asset)->firstOrFail();
        $disk = Storage::disk($file->disk);
        abort_unless($disk->exists($file->path), 404);

        return $disk->response($file->path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function rsvp(Request $request, $slug)
    {
        $i = $this->live($slug);
        $live = $i->settings['studio_live'];
        abort_unless($live['rsvp'] && collect($live['sections'])->contains('kind', 'rsvp'), 404);
        $data = $request->validate(['guest_name' => 'required|string|max:150', 'attendance_status' => 'required|in:hadir,berhalangan']);
        $i->rsvps()->create($data + ['attendee_count' => $data['attendance_status'] === 'hadir' ? 1 : 0, 'submitted_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
