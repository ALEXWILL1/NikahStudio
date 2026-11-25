import { Head } from '@inertiajs/react';

type DashboardProps = {
    name: string;
    role: 'admin' | 'client';
};

export default function Dashboard({ name, role }: DashboardProps) {
    const isAdmin = role === 'admin';
    const title = isAdmin ? 'Dashboard Admin' : 'Undangan Saya';

    return (
        <>
            <Head title={title} />
            <main className="flex-1 bg-[#faf6ee] p-6 text-[#30271e] sm:p-8">
                <section className="rounded-2xl border border-[#e9dbc2] bg-white p-6 sm:p-8">
                    <p className="text-sm font-medium text-[#76582e]">
                        {title}
                    </p>
                    <h1 className="mt-2 text-3xl font-semibold">Halo, {name}</h1>
                    <p className="mt-3 text-sm text-[#625548]">
                        {isAdmin
                            ? 'Panel pengelolaan NikahStudio.'
                            : 'Kelola undangan pernikahanmu di NikahStudio.'}
                    </p>
                </section>
            </main>
        </>
    );
}