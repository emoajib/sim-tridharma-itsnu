import ApplicationLogo from '@/Components/ApplicationLogo';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome({ auth }: PageProps<Record<string, unknown>>) {
    const { props } = usePage();
    const appSettings = (props.appSettings as any) || {};
    const logoUrl = appSettings?.logo_path ? '/storage/' + appSettings.logo_path : null;

    return (
        <>
            <Head title="Selamat Datang" />
            <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-indigo-50">
                <div className="w-full max-w-lg px-6 text-center">
                    <div className="flex justify-center mb-8">
                        <ApplicationLogo logoUrl={logoUrl} className="scale-150" />
                    </div>

                    <h1 className="text-3xl font-black text-gray-800 tracking-tight">
                        Sistem Akreditasi Multi-Agent
                    </h1>
                    <p className="mt-3 text-sm text-gray-500 font-medium leading-relaxed">
                        Platform terintegrasi untuk monitoring, evaluasi, dan prediksi akreditasi program studi berbasis AI
                    </p>

                    <div className="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-8 py-3 text-sm font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all uppercase tracking-widest"
                            >
                                Masuk Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('login')}
                                    className="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-8 py-3 text-sm font-black text-white hover:bg-indigo-700 shadow-xl shadow-indigo-100 transition-all uppercase tracking-widest"
                                >
                                    Masuk
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="inline-flex items-center justify-center rounded-xl border-2 border-indigo-200 px-8 py-3 text-sm font-black text-indigo-600 hover:border-indigo-400 hover:bg-indigo-50 transition-all uppercase tracking-widest"
                                >
                                    Daftar
                                </Link>
                            </>
                        )}
                    </div>

                    <div className="mt-16 text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                        &copy; ITSNU Pekalongan
                    </div>
                </div>
            </div>
        </>
    );
}
