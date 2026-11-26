import { Circle, Diamond, Flower2, Minus, Sprout, Square, TreeDeciduous } from 'lucide-react';
import { useRef } from 'react';
import type { PointerEvent } from 'react';
import './studio.css';
import './atelier.css';
import { backgrounds, illustrations, material } from './materials';

export const motifs = { branch: [Sprout, 'Ranting'], tree: [TreeDeciduous, 'Pohon'], flower: [Flower2, 'Bunga'], diamond: [Diamond, 'Berlian'], circle: [Circle, 'Lingkaran'], square: [Square, 'Persegi'], line: [Minus, 'Garis'] } as const;
export type Item = { id: string; type: 'text'|'shape'|'photo'|'illustration'; value: string; x:number; y:number; w:number; size:number; angle:number; color:string; font:string; frame:string };
export type Section = { id:string; kind:string; label:string; bg:string; backdrop?:string|null; treatment?:string; items:Item[] };
export const path = (id:number) => '/client/undangan/' + id;
export async function api<T>(url:string, method:string, data:object):Promise<T> {
    const token = document.cookie.split('; ').find(v => v.startsWith('XSRF-TOKEN='))?.slice(11) ?? '';
    const headers:Record<string,string> = { Accept:'application/json', 'X-XSRF-TOKEN':decodeURIComponent(token) };
    if (!(data instanceof FormData)) headers['Content-Type']='application/json';
    const response = await fetch(url,{method,headers,body:data instanceof FormData ? data : JSON.stringify(data)});
    const body = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(Object.values(body.errors ?? {}).flat()[0] as string || body.message || 'Permintaan gagal. Coba lagi.');
    return body as T;
}
export function message(e:unknown) { return e instanceof Error ? e.message : 'Permintaan gagal.'; }
export function Canvas({ section, id, selected, pick, move }: {section:Section; id:number; selected?:string; pick?:(id:string)=>void; move?:(id:string,x:number,y:number)=>void}) {
    const drag = useRef<{id:string; sx:number; sy:number; x:number; y:number; width:number; height:number}|null>(null);
    function start(e:PointerEvent<HTMLDivElement>, item:Item) {
        if (!pick || !e.isPrimary || e.button !== 0) return;
        pick(item.id);
        const rect = e.currentTarget.parentElement!.getBoundingClientRect();
        drag.current = {id:item.id,sx:e.clientX,sy:e.clientY,x:item.x,y:item.y,width:rect.width,height:rect.height};
        e.currentTarget.setPointerCapture(e.pointerId);
    }
    function stop(e:PointerEvent<HTMLDivElement>, commit:boolean) {
        const d = drag.current;
        if (!d) return;
        const clamp = (n:number) => Math.min(99,Math.max(0,n));
        const x=clamp(d.x+100*(e.clientX-d.sx)/d.width), y=clamp(d.y+100*(e.clientY-d.sy)/d.height);
        e.currentTarget.style.translate='';
        drag.current=null;
        if (commit && (x!==d.x || y!==d.y)) move?.(d.id,x,y);
    }
    return <div className="ns-canvas" style={{background:section.bg}}>
        {section.backdrop && backgrounds[section.backdrop] && <div aria-hidden="true" className={'ns-backdrop treatment-'+(section.treatment || 'full')} style={{backgroundImage:'url('+material('latar',section.backdrop)+')'}}/>}
        {section.items.map(item=>{
        const Icon = Object.hasOwn(motifs, item.value) ? motifs[item.value as keyof typeof motifs][0] : Flower2;
        return <div key={item.id} className={'ns-item '+(pick?'editable ':'')+(selected===item.id?'chosen':'')} role={pick?'button':undefined} tabIndex={pick?0:undefined}
            aria-label={pick ? (item.type==='text'?item.value:item.type==='photo'?'Foto':item.value) : undefined}
            onKeyDown={e=>{if (e.key==='Enter' || e.key===' ') {if(pick)e.preventDefault();pick?.(item.id);}}}
            onPointerDown={e=>start(e,item)} onPointerMove={e=>{const d=drag.current;if(d && d.id===item.id)e.currentTarget.style.translate=(e.clientX-d.sx)+'px '+(e.clientY-d.sy)+'px';}}
            onPointerUp={e=>stop(e,true)} onPointerCancel={e=>stop(e,false)} onLostPointerCapture={e=>stop(e,false)}
            style={{left:item.x+'%',top:item.y+'%',width:item.w+'%',transform:'rotate('+item.angle+'deg)',color:item.color,fontFamily:item.font,fontSize:item.size+'cqw'}}>
            {item.type==='illustration' && illustrations[item.value]?<img className="ns-illustration" draggable={false} alt="" src={material('ornamen',item.value)}/>:item.type==='photo'?<img src={'/foto-undangan/'+id+'/'+item.value} draggable={false} alt="Foto undangan" className={'frame-'+item.frame}/>:item.type==='shape'?<Icon strokeWidth={1.15} aria-hidden="true"/>:item.value}
        </div>;
    })}</div>;
}
