import { Head, Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { ArrowLeft, ArrowRight, BookOpen, Check, ChevronLeft, ChevronRight, FolderOpen, Home as HomeIcon, LayoutGrid, LogOut, PanelLeftClose, Plus, Search, Settings } from 'lucide-react';
import { Canvas, api, message, path } from '../../studio/design';
import type { Section } from '../../studio/design';

type Screen = 'home'|'projects'|'templates'|'create';
type Template = {id:number; name:string; category:string; tier:string; allowed:boolean; samples:Section[]};
type Invitation = {id:number; title:string; published:boolean; url:string; rsvps:number; cover:Section|null};
type Props = {owner:string; screen:Screen; templates:Template[]; invitations:Invitation[]; limit:number};
const pages = {
    home: ['/client/dashboard','Beranda',HomeIcon],
    projects: ['/client/undangan/koleksi','Undangan saya',FolderOpen],
    templates: ['/client/undangan/template','Template',LayoutGrid],
    create: ['/client/undangan/baru','Buat undangan',Plus],
} as const;
const categories:Record<string,string> = {floral:'Floral',garden:'Taman',classic:'Klasik',minimalist:'Minimalis',rustic:'Rustik',luxury:'Mewah',heritage:'Warisan'};
const createUrl = (id:number) => pages.create[0]+'?template='+id;

function TemplateCard({template}:{template:Template}) {
    return <Link className="at-card" href={createUrl(template.id)}>
        <div className="at-card-print"><Canvas id={0} section={template.samples[0]}/></div>
        <div className="at-card-meta"><div><small>{categories[template.category] ?? 'Koleksi'}</small><h3>{template.name}</h3></div><span>{template.tier==='free'?'Gratis':'Premium'}</span></div>
    </Link>;
}
function ProjectCard({project}:{project:Invitation}) {
    return <article className="at-project">
        <Link href={path(project.id)} className="at-project-cover" aria-label={'Edit '+project.title}>{project.cover?<Canvas id={project.id} section={project.cover}/>:<BookOpen/>}</Link>
        <div><small>{project.published?'Terbit':'Draf'} · {project.rsvps} jawaban RSVP</small><h3>{project.title}</h3><Link href={path(project.id)}>Lanjutkan edit →</Link>{project.published && <a href={project.url}>Buka undangan ↗</a>}</div>
    </article>;
}
function Create({choice, full}:{choice:Template|undefined;full:boolean}) {
    const [sample,setSample]=useState(0), [busy,setBusy]=useState(false), [error,setError]=useState('');
    const locked=Boolean(choice&&!choice.allowed);
    return <div className="at-create">
        <div className="at-proof">{choice?<><Canvas id={0} section={choice.samples[sample]}/><nav>
            <button aria-label="Bagian sebelumnya" disabled={sample===0} onClick={()=>setSample(sample-1)}><ChevronLeft/></button>
            <span>{choice.samples[sample].label} · {sample+1}/{choice.samples.length}</span>
            <button aria-label="Bagian berikutnya" disabled={sample===choice.samples.length-1} onClick={()=>setSample(sample+1)}><ChevronRight/></button></nav></>:<div className="at-blank"><Plus/><span>Kanvas kosong</span></div>}</div>
        <form onSubmit={async e=>{
            e.preventDefault();const data=new FormData(e.currentTarget);setBusy(true);setError('');
            try { const result=await api<{url:string}>('/client/undangan','POST',{first:data.get('first'),second:data.get('second'),template_id:choice?.id??null}); window.location.assign(result.url); }
            catch(err){setError(message(err));setBusy(false);}
        }}>
            <Link href={pages.templates[0]}><ArrowLeft size={15}/> Koleksi template</Link>
            <h1>{choice?.name ?? 'Mulai dari kosong'}</h1><p>{choice?'Nama kalian akan terisi di salinan template ini.':'Isi nama kalian untuk membuka kanvas pertama.'}</p>
            <label>Nama mempelai pertama<input name="first" maxLength={65} required placeholder="Nama panggilan"/></label>
            <label>Nama mempelai kedua<input name="second" maxLength={65} required placeholder="Nama panggilan"/></label>
            <button className="primary" disabled={busy||locked||full}>{busy?'Membuat draf…':locked?'Khusus paket Premium':full?'Kuota undangan penuh':'Buat dan buka editor'} <ArrowRight size={17}/></button>
            {full&&<Link href={pages.projects[0]}>Buka undangan saya</Link>}{error&&<p role="alert">{error}</p>}
        </form>
    </div>;
}

export default function Home({owner,screen,templates,invitations,limit}:Props) {
    const {url}=usePage();
    const params=new URL(url,'http://localhost').searchParams;
    const choice=templates.find(t=>String(t.id)===params.get('template'));
    const [small,setSmall]=useState(false),[query,setQuery]=useState(''),[filter,setFilter]=useState('Semua');
    useEffect(()=>{setSmall(localStorage.getItem('nikah-menu')==='ringkas');},[]);
    useEffect(()=>{setQuery('');setFilter('Semua');},[screen]);
    const normalized=query.toLocaleLowerCase().trim();
    const filtered=templates.filter(t=>t.name.toLocaleLowerCase().includes(normalized)&&(filter==='Semua'||(categories[t.category]??'Koleksi')===filter));
    const projects=invitations.filter(i=>i.title.toLocaleLowerCase().includes(normalized));
    return <div className={'ns at-dashboard'+(small?' trimmed':'')}><Head title={pages[screen][1]}/>
        <aside className="at-side"><Link className="at-logo" href={pages.home[0]} aria-label="NikahStudio"><b>N</b><span>NikahStudio</span></Link>
            <nav>{(['create','home','projects','templates'] as const).map(k=>{const [href,label,Icon]=pages[k];return <Link key={k} href={href} title={label} aria-label={label} aria-current={k===screen?'page':undefined} className={'at-nav '+k}><Icon/><span>{label}</span></Link>;})}</nav>
            <div className="at-lower"><button className="at-nav at-collapse" aria-expanded={!small} title="Ubah ukuran menu" onClick={()=>{setSmall(!small);localStorage.setItem('nikah-menu',small?'penuh':'ringkas');}}><PanelLeftClose/><span>Ringkas menu</span></button>
            <Link href="/settings/profile" className="at-nav" title="Akun saya" aria-label="Akun saya"><Settings/><span>Akun saya</span></Link>
            <Link as="button" href="/logout" method="post" className="at-nav" title="Keluar" aria-label="Keluar"><LogOut/><span>Keluar</span></Link></div>
        </aside>
        <main className="at-main" key={screen}><header className="at-top"><span>Studio <i>/</i> {pages[screen][1]}</span><Link href="/settings/profile" title="Akun saya" aria-label="Akun saya">{owner.slice(0,1).toUpperCase()}</Link></header>
        {screen==='home'&&<>
            <section className="at-hero"><div><p>Halo, {owner}</p><h1>Studio <em>undangan</em></h1><Link href={pages.templates[0]}>Jelajahi template <ArrowRight size={16}/></Link></div>
                <div className="at-paper" aria-hidden="true"><img src="/nikahstudio/ornamen/jasmine.webp" alt=""/><i>The Wedding of</i><b>Alya &amp; Raka</b><img src="/nikahstudio/ornamen/rings.webp" alt=""/></div></section>
            <div className="at-heading"><h2>Koleksi pilihan</h2><Link href={pages.templates[0]}>Semua template <ArrowRight/></Link></div>
            <div className="at-grid">{templates.slice(0,4).map(t=><TemplateCard key={t.id} template={t}/>)}</div>
            <div className="at-heading"><h2>Lanjutkan mengedit</h2><Link href={pages.projects[0]}>Undangan saya <ArrowRight/></Link></div>
            {invitations.length?<div className="at-projects">{invitations.slice(0,3).map(i=><ProjectCard key={i.id} project={i}/>)}</div>:<Link className="at-start" href={pages.create[0]}><Plus/> Buat undangan pertamamu <ArrowRight/></Link>}
        </>}
        {screen==='templates'&&<><h1>Koleksi <em>template</em></h1><label className="at-search"><Search/><input type="search" placeholder="Cari nama template" aria-label="Cari nama template" value={query} onChange={e=>setQuery(e.target.value)}/></label>
            <div className="at-filters">{['Semua',...new Set(templates.map(t=>categories[t.category]??'Koleksi'))].map(f=><button key={f} aria-pressed={filter===f} onClick={()=>setFilter(f)}>{filter===f&&<Check size={14}/>} {f}</button>)}</div>
            <div className="at-grid">{filtered.map(t=><TemplateCard key={t.id} template={t}/>)}</div>{!filtered.length&&<p>Belum ada template yang cocok.</p>}</>}
        {screen==='projects'&&<><h1>Undangan <em>saya</em></h1><div className="at-heading"><label className="at-search"><Search/><input type="search" placeholder="Cari undangan" aria-label="Cari undangan" value={query} onChange={e=>setQuery(e.target.value)}/></label><small>{invitations.length} dari {limit} undangan</small></div>
            <div className="at-projects">{projects.map(i=><ProjectCard key={i.id} project={i}/>)}</div>{!projects.length&&<p>{query?'Undangan tidak ditemukan.':'Kamu belum punya undangan.'}</p>}<Link className="at-start" href={pages.create[0]}><Plus/> Buat dari kosong</Link></>}
        {screen==='create'&&(params.has('template')&&!choice?<p>Template ini sudah tidak tersedia. <Link href={pages.templates[0]}>Pilih template lain</Link></p>:<Create key={choice?.id??0} choice={choice} full={invitations.length>=limit}/>)}
        </main>
    </div>;
}
