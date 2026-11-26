import { Head } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { ArrowLeft, Undo2, Smartphone, ImagePlus, Type } from 'lucide-react';
import { backgrounds, backgroundModes, illustrations, material } from '../../studio/materials';
import { api, Canvas, message, motifs, path } from '../../studio/design';
import type { Item, Section } from '../../studio/design';

type Document = {title:string; sections:Section[]};
type Props = Document & { id:number; photos:number[]; published:boolean; revision:number; url:string };
const kinds:Record<string,string>={custom:'Halaman bebas',cover:'Sampul',couple:'Pasangan',event:'Acara & lokasi',gallery:'Galeri',rsvp:'RSVP'};
const fonts = ['Georgia','Garamond','Arial','Verdana','monospace','cursive'];
export default function Editor(props:Props) {
    const [doc,setDoc]=useState<Document>({title:props.title,sections:props.sections});
    const [page,setPage]=useState(props.sections[0].id), [selected,select]=useState(''), [preview,setPreview]=useState(false);
    const [tool,setTool]=useState('Halaman');
    const [photos,setPhotos]=useState(props.photos), [publicUrl,setUrl]=useState(props.published?props.url:''), [busy,setBusy]=useState(false);
    const [canUndo,setCanUndo]=useState(false), [status,setStatus]=useState('Tersimpan'), [feedback,setFeedback]=useState('');
    const current=useRef(doc), saved=useRef(doc), version=useRef(props.revision), queue=useRef(Promise.resolve()), history=useRef<Document[]>([]);
    const section=doc.sections.find(s=>s.id===page) ?? doc.sections[0], item=section.items.find(i=>i.id===selected);
    const base=path(props.id);
    function change(next:Document) {
        history.current=[...history.current.slice(-39), current.current];
        setCanUndo(true);
        current.current=next;setDoc(next);setStatus('Perubahan belum tersimpan');
    }
    function updateSection(patch:Partial<Section>) {
        change({...doc,sections:doc.sections.map(s=>s.id===section.id?{...s,...patch}:s)});
    }
    function updateItem(patch:Partial<Item>, target=selected) {
        updateSection({items:section.items.map(i=>i.id===target?{...i,...patch}:i)});
    }
    function add(type:Item['type'], value:string) {
        if(section.items.length >= 60){setFeedback('Halaman ini sudah berisi 60 elemen.');return;}
        const i:Item={id:crypto.randomUUID(),type,value,x:20,y:32,w:type==='text'?60:36,size:6,angle:0,color:'#624831',font:'Georgia',frame:'soft'};
        updateSection({items:[...section.items,i]});select(i.id);
    }
    function save() {
        const task=queue.current.catch(()=>{}).then(async()=>{
            const snapshot=current.current;
            if(saved.current===snapshot)return;
            setStatus('Menyimpan…');
            const r=await api<{revision:number}>(base,'PUT',{...snapshot,revision:version.current});
            version.current=r.revision;saved.current=snapshot;
            setStatus(current.current===snapshot?'Tersimpan':'Perubahan belum tersimpan');
        });
        queue.current=task;
        return task;
    }
    useEffect(()=>{
        if(current.current===saved.current)return;
        const t=setTimeout(()=>{void save().catch(err=>setStatus(message(err)));},850);
        return ()=>clearTimeout(t);
        // Changes are serialized against the most recent server revision.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    },[doc]);
    useEffect(()=>{
        function guard(e:BeforeUnloadEvent){if(current.current!==saved.current){e.preventDefault();e.returnValue='';}}
        window.addEventListener('beforeunload',guard);return ()=>window.removeEventListener('beforeunload',guard);
    },[]);
    async function publish(){
        setBusy(true);setFeedback('');
        try{await save();const r=await api<{url:string}>(base+'/terbit','POST',{revision:version.current});setUrl(r.url);setFeedback('Undangan berhasil diterbitkan.');}
        catch(e){setFeedback(message(e));}finally{setBusy(false);}
    }
    async function upload(file?:File){
        if(!file)return;setBusy(true);setFeedback('');
        try{const form=new FormData();form.set('photo',file);const r=await api<{id:number}>(base+'/foto','POST',form);setPhotos(p=>[...p,r.id]);add('photo',String(r.id));}
        catch(e){setFeedback(message(e));}finally{setBusy(false);}
    }
    function reorder(direction:number) {
        const arr=[...doc.sections], from=arr.indexOf(section), to=from+direction;
        if(to<0||to>=arr.length)return;
        [arr[from],arr[to]]=[arr[to],arr[from]];change({...doc,sections:arr});
    }
    function addSection(kind:string){
        if(!kind)return;
        const s:Section={id:crypto.randomUUID(),kind,label:kinds[kind],bg:section.bg,items:[]};
        if (kind !== 'custom') {
            s.items = [{
                id: crypto.randomUUID(), type: 'text', value: s.label, x: 10, y: 15,
                w: 80, size: 8, angle: 0, color: '#624831', font: 'Georgia', frame: 'soft',
            }];
        }
        change({...doc,sections:[...doc.sections,s]});setPage(s.id);select('');
    }
    return <main className="ns ns-editor" aria-busy={busy}><Head title={doc.title}/>
        <header className="ns-bar"><a href="/client/dashboard" aria-label="Kembali ke dashboard"><ArrowLeft/></a><strong>NikahStudio</strong>
        <small role="status">{status}</small><button disabled={!canUndo||busy} title="Urungkan" aria-label="Urungkan" onClick={()=>{const d=history.current.pop();setCanUndo(history.current.length > 0);if(d){current.current=d;setDoc(d);setStatus('Perubahan belum tersimpan');}}}><Undo2/></button>
        <button onClick={()=>setPreview(!preview)}><Smartphone/>{preview?'Kembali ke editor':'Pratinjau HP'}</button>
        <button disabled={busy} onClick={()=>void save().catch(e=>setStatus(message(e)))}>Simpan</button>
        <button className="primary" disabled={busy} onClick={()=>void publish()}>{publicUrl?'Terbitkan perubahan':'Terbitkan'}</button></header>
        {(publicUrl||feedback) && <div className="ns-notice" role="status">{feedback} {publicUrl && <><a target="_blank" rel="noreferrer" href={publicUrl}>Buka undangan ↗</a> <button onClick={async()=>{try{await navigator.clipboard.writeText(publicUrl);setFeedback('Tautan tersalin.');}catch{setFeedback(publicUrl);}}}>Salin tautan</button></>}</div>}
        {preview?<div className="ns-phone">{doc.sections.map(s=><div key={s.id}><Canvas id={props.id} section={s}/>{s.kind==='rsvp'&&<p className="ns-empty">Konfirmasi kehadiran tamu</p>}</div>)}</div>:
        <div className="ns-work" inert={busy}>
        <aside className="ns-tools at-palette">
        <nav className="at-tabs" aria-label="Alat editor">{['Halaman','Elemen','Foto','Latar'].map(t=><button key={t} aria-pressed={tool===t} onClick={()=>setTool(t)}>{t}</button>)}</nav>
        {tool==='Halaman' && <><label>Nama undangan<input value={doc.title} maxLength={150} onChange={e=>change({...doc,title:e.target.value})}/></label>
        <h3>Halaman</h3>{doc.sections.map((s,n)=><button key={s.id} className={s.id===section.id?'active':''} onClick={()=>{setPage(s.id);select('');}}>{n+1}. {s.label}</button>)}
        <label>Tambah halaman<select value="" disabled={doc.sections.length>=24} onChange={e=>addSection(e.target.value)}><option value="">Pilih bagian…</option>{Object.entries(kinds).map(([k,v])=><option key={k} value={k}>{v}</option>)}</select></label>
        <div className="ns-actions"><button onClick={()=>reorder(-1)}>Naik</button><button onClick={()=>reorder(1)}>Turun</button></div>
        <button disabled={doc.sections.length===1} onClick={()=>{if(window.confirm('Hapus halaman ini dari draf?'))change({...doc,sections:doc.sections.filter(s=>s.id!==section.id)});}}>Hapus halaman</button>
        </>}
        {tool==='Elemen' && <><h3>Teks & ilustrasi</h3><button disabled={section.items.length>=60} onClick={()=>add('text','Tulis teks di sini')}><Type/> Tambah teks</button>
        <div className="at-swatches">{Object.entries(illustrations).map(([k,label])=><button key={k} onClick={()=>add('illustration',k)} title={label}><img src={material('ornamen',k)} alt=""/><span>{label}</span></button>)}</div>
        <h3>Bentuk & ornamen</h3><div className="ns-motifs">{Object.entries(motifs).map(([k,[Icon,label]])=><button key={k} title={label} aria-label={label} onClick={()=>add('shape',k)}><Icon/></button>)}</div></>}
        {tool==='Foto' && <><h3>Foto saya</h3>
        <label className="ns-upload"><ImagePlus/> Unggah foto<input type="file" accept="image/jpeg,image/png,image/webp" onChange={e=>{void upload(e.target.files?.[0]);e.target.value='';}}/></label>
        <div className="ns-photos">{photos.map(id=><button key={id} title="Pakai foto" onClick={()=>add('photo',String(id))}><img alt="Foto koleksi" src={'/foto-undangan/'+props.id+'/'+id}/></button>)}</div>
</>}
        {tool==='Latar' && <><h3>Latar halaman</h3><label>Gaya foto<select value={section.treatment || 'full'} onChange={e=>updateSection({treatment:e.target.value})}>{Object.entries(backgroundModes).map(([k,v])=><option key={k} value={k}>{v}</option>)}</select></label>
        <div className="at-swatches">{Object.entries(backgrounds).map(([k,label])=><button key={k} aria-pressed={section.backdrop===k} onClick={()=>updateSection({backdrop:k,treatment:section.treatment||'full'})}><img src={material('latar',k)} alt=""/><span>{label}</span></button>)}</div>
        <button disabled={!section.backdrop} onClick={()=>updateSection({backdrop:null})}>Hapus foto latar</button></>}
        </aside>
        <section className="ns-artboard"><small>{section.label}</small><Canvas id={props.id} section={section} selected={selected} pick={select} move={(id,x,y)=>updateItem({x,y},id)}/></section>
        <aside className="ns-tools"><h3>Pengaturan halaman</h3><label>Nama bagian<input value={section.label} maxLength={80} onChange={e=>updateSection({label:e.target.value})}/></label>
        <label>Warna latar<input type="color" value={section.bg} onChange={e=>updateSection({bg:e.target.value})}/></label>
        <h3>Lapisan</h3><select aria-label="Pilih elemen" value={item?.id??''} onChange={e=>select(e.target.value)}><option value="">Pilih elemen…</option>{section.items.map((i,n)=><option key={i.id} value={i.id}>{n+1}. {i.type==='text'?i.value:i.type==='photo'?'Foto':illustrations[i.value]||i.value}</option>)}</select>
        {item && <>{item.type==='text' && <><label>Teks<textarea value={item.value??''} maxLength={1500} onChange={e=>updateItem({value:e.target.value})}/></label><label>Font<select value={item.font} onChange={e=>updateItem({font:e.target.value})}>{fonts.map(f=><option key={f}>{f}</option>)}</select></label></>}
        {(item.type==='text'||item.type==='shape') && <label>Warna elemen<input type="color" value={item.color} onChange={e=>updateItem({color:e.target.value})}/></label>}
        {item.type==='photo' && <label>Bingkai<select value={item.frame} onChange={e=>updateItem({frame:e.target.value})}>{Object.entries({soft:'Sudut lembut',square:'Persegi',arch:'Lengkung',round:'Lingkaran'}).map(([k,v])=><option key={k} value={k}>{v}</option>)}</select></label>}
        {([['w','Lebar',4,100],['size','Ukuran teks',2,20],['angle','Rotasi',-180,180],['x','Posisi horizontal',0,99],['y','Posisi vertikal',0,99]] as const).filter(([k])=>k!=='size'||item.type==='text').map(([k,label,min,max])=><label key={k}>{label} · {Math.round(item[k])}<input type="range" min={min} max={max} value={item[k]} onChange={e=>updateItem({[k]:Number(e.target.value)})}/></label>)}
        <button onClick={()=>updateSection({items:[...section.items.filter(i=>i.id!==item.id),item]})}>Ke depan</button><button onClick={()=>updateSection({items:[item,...section.items.filter(i=>i.id!==item.id)]})}>Ke belakang</button>
        <button onClick={()=>{updateSection({items:section.items.filter(i=>i.id!==item.id)});select('');}}>Hapus elemen</button></>}
        {!item && <small>Pilih elemen di kanvas atau tambahkan teks dan foto.</small>}</aside>
        </div>}</main>;
}
