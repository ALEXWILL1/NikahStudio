import { Head } from '@inertiajs/react';
import { useState } from 'react';
import { api, Canvas, message } from '../../studio/design';
import type { Section } from '../../studio/design';
export default function Guest({id,title,slug,sections,watermark,rsvp}:{id:number;title:string;slug:string;sections:Section[];watermark:boolean;rsvp:boolean}) {
    const [sent,setSent]=useState(false), [busy,setBusy]=useState(false), [error,setError]=useState('');
    return <main className="ns ns-guest"><Head title={title}/><div>{sections.map(s=><section key={s.id} aria-label={s.label}><Canvas id={id} section={s}/>
    {s.kind==='rsvp' && rsvp && (sent?<p className="ns-rsvp" role="status">Terima kasih, jawabanmu sudah tercatat.</p>:<form className="ns-rsvp" onSubmit={async e=>{e.preventDefault();const data=new FormData(e.currentTarget);setBusy(true);setError('');try{await api('/u/'+slug+'/rsvp','POST',Object.fromEntries(data));setSent(true);}catch(err){setError(message(err));}finally{setBusy(false);}}}>
    <h2>Konfirmasi kehadiran</h2><label>Nama tamu<input name="guest_name" required maxLength={150}/></label><label>Kehadiran<select name="attendance_status"><option value="hadir">Saya akan hadir</option><option value="berhalangan">Saya berhalangan hadir</option></select></label><button className="primary" disabled={busy}>Kirim jawaban</button>{error&&<p role="alert">{error}</p>}</form>)}</section>)}
    {watermark && <footer>Undangan dibuat dengan NikahStudio</footer>}</div></main>;
}
