<?php

namespace App\Support;

use App\Models\Template;
use Illuminate\Support\Str;

class InvitationDesign
{
    public static function section(string $kind, string $label, string $bg, array $items): array
    {
        return ['id' => (string) Str::uuid(), 'kind' => $kind, 'label' => $label, 'bg' => $bg, 'items' => $items];
    }

    private static function item(string $type, string $value, int $x, int $y, int $w, int $size, string $color, string $font = 'Georgia'): array
    {
        return compact('type', 'value', 'x', 'y', 'w', 'size', 'color', 'font') + [
            'id' => (string) Str::uuid(), 'angle' => 0, 'frame' => 'soft',
        ];
    }

    public static function make(?Template $template, string $first, string $second): array
    {
        $colors = $template?->config['colors'] ?? [];
        $bg = $colors['background'] ?? '#fffaf0';
        if (! $template || $template->view_key === 'studio-blank') {
            return [self::section('cover', 'Sampul', $bg, [])];
        }
        [$photo, $mode, $paper, $ink, $flower, $secondary, $font] = match ($template->view_key) {
            'minimalist-white' => ['zaitun','band','#fffdf5','#383e32','olive','jasmine','Georgia'],
            'modern-luxury' => ['lilin','full','#35241e','#fff2d5','scroll','rings','Garamond'],
            'rustic-wedding' => ['rimba','full','#2e3e31','#fff6da','palm','jasmine','Georgia'],
            'hortensia-biru' => ['hortensia','arch','#eaf1f1','#324b51','hydrangea','orchid','Garamond'],
            'pusaka-toba' => ['toba','band','#52332b','#fff2d9','house','scroll','Garamond'],
            'beludru-merlot' => ['beludru','full','#4e2132','#fff1d6','peony','scroll','Garamond'],
            'kebun-senja' => ['senja','oval','#eee7d8','#49412f','jasmine','olive','Georgia'],
            'janji-champagne' => ['cincin','oval','#fcf1de','#634631','rings','orchid','Garamond'],
            default => ['mawar','arch',$bg,$colors['text'] ?? '#52392c','ivory','jasmine','Georgia'],
        };

        $text = fn ($value, $y, $size, $color = null) =>
            self::item('text', $value, 7, $y, 86, $size, $color ?? $ink, $font);
        $art = fn ($name, $x, $y, $w) =>
            self::item('illustration', $name, $x, $y, $w, 6, $ink);
        $panel = fn ($kind, $label, $background, $items, $image, $treatment) =>
            self::section($kind, $label, $background, $items) + ['backdrop' => $image, 'treatment' => $treatment];

        $name = $first.' & '.$second;
        $cover = match ($mode) {
            'full' => [$art($secondary, 7, 5, 24), $text('THE WEDDING OF', 48, 3),
                $text($name, 56, 9), $art($flower, 65, 75, 32)],
            'band' => [$text('UNDANGAN PERNIKAHAN', 8, 3), $text($name, 17, 8),
                $art($flower, 35, 74, 30)],
            'oval' => [$art($secondary, 2, 12, 24), $text('THE WEDDING OF', 63, 3),
                $text($name, 69, 8), $art($flower, 40, 85, 20)],
            default => [$art($flower, 66, 36, 32), $text('UNDANGAN PERNIKAHAN', 57, 3),
                $text($name, 64, 9), $art($secondary, 33, 83, 34)],
        };
        $dark = '#48382f';
        return [
            $panel('cover','Sampul',$paper,$cover,$photo,$mode),
            $panel('couple','Kedua mempelai','#fbf5ea',[
                $art($flower, 0, 0, 36), $text('KEDUA MEMPELAI', 26, 3, $dark),
                $text($first, 38, 9, $dark), $text('&', 51, 5, $dark), $text($second, 61, 9, $dark),
                $art($secondary, 65, 75, 33),
            ],$photo,'faded'),
            $panel('event','Acara dan lokasi','#fcf7ee',[
                $art($secondary, 39, 5, 22), $text('Akad & resepsi', 27, 8, $dark),
                $text('Tanggal dan waktu acara', 46, 4, $dark), $text('Nama tempat dan alamat', 57, 4, $dark),
                $art($flower, 59, 73, 38),
            ],'zaitun','faded'),
            $panel('gallery','Galeri',$paper,[$text('Galeri kami', 9, 8),
                $art($secondary, 37, 77, 26)],$photo,'band'),
            $panel('rsvp','Konfirmasi kehadiran','#f9f2e4',[$art($flower, 31, 9, 38),
                $text('Kami menantikan kehadiranmu', 53, 7, $dark), $text($name, 78, 4, $dark)],
                $photo,'faded'),
        ];
    }
}
