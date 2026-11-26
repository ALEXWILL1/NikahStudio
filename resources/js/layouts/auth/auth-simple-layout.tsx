import type { ReactNode } from 'react';
import './nikah-auth.css';

type Props = { title?: string; description?: string; children: ReactNode };

export default function AuthSimpleLayout({ title, description, children }: Props) {
    return (
        <main className="nikah-auth">
            <div aria-hidden="true" className="nikah-photo" />
            <aside className="nikah-cover">
                <div className="nikah-brand"><span aria-hidden="true">N</span>NikahStudio</div>
           <p className="nikah-caption" lang="en">
    Digital wedding invitation
</p>
            </aside>
            <section className="nikah-panel">
                <div className="nikah-form">
                    <div className="nikah-ornament" aria-hidden="true"><b /><i /></div>
                    {title && <h1>{title}</h1>}
                    {description && <p className="nikah-description">{description}</p>}
                    {children}
                </div>
            </section>
        </main>
    );
}
