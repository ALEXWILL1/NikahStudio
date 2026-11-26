import { Form, Head, Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { login, logout } from '@/routes';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import type { Auth } from '@/types';

export default function VerifyEmail({ status }: { status?: string }) {
    const { auth } = usePage<{ auth: Auth }>().props;
    const [leaving, setLeaving] = useState(false);
    const [error, setError] = useState('');

    async function switchAccount() {
        setLeaving(true);
        setError('');
        try {
            const cookie = document.cookie.split('; ').find(value => value.startsWith('XSRF-TOKEN='));
            const response = await fetch(logout.url(), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent(cookie?.slice('XSRF-TOKEN='.length) ?? ''),
                },
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error();
            window.location.replace(login.url());
        } catch {
            setLeaving(false);
            setError('Belum berhasil keluar. Muat ulang halaman ini, lalu coba lagi.');
        }
    }

    return (
        <>
            <Head title="Verifikasi email" />
            <p className="mb-5 break-words text-sm" style={{ color: '#523728' }}>
                Alamat email akun ini <strong>{auth.user.email}</strong>
            </p>
            {status === 'verification-link-sent' && (
                <p role="status" className="mb-4 text-sm" style={{ color: '#3e5739' }}>
                    Tautan verifikasi sudah dikirim ulang. Periksa kotak masuk atau folder spam.
                </p>
            )}
            <Form {...send.form()}>
                {({ processing }) => (
                    <button type="submit" disabled={processing || leaving}
                        className="w-full rounded-lg px-4 py-3 font-medium disabled:opacity-60"
                        style={{ background: '#563824', color: '#fff9ee' }}>
                        {processing ? 'Mengirim…' : 'Kirim ulang email verifikasi'}
                    </button>
                )}
            </Form>
            <div className="mt-5 grid gap-3 text-center">
                <Link href={edit()} className="text-sm underline underline-offset-4" style={{ color: '#543521' }}>
                    Salah ketik? Perbaiki alamat email
                </Link>
                <button type="button" disabled={leaving} onClick={switchAccount}
                    className="w-full rounded-lg border px-4 py-3 font-medium disabled:opacity-60"
                    style={{ color: '#4d301d', borderColor: '#ae9271', background: '#fffaf1' }}>
                    {leaving ? 'Sedang keluar…' : 'Keluar dan masuk dengan akun lain'}
                </button>
                {error && <p role="alert" className="text-sm text-red-800">{error}</p>}
            </div>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Verifikasi email',
    description: 'Buka tautan di email untuk mengaktifkan akun NikahStudio.',
};
