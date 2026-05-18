import { HTMLAttributes } from 'react';

interface Props extends HTMLAttributes<HTMLDivElement> {
    isDark?: boolean;
    logoUrl?: string | null;
}

export default function ApplicationLogo({ isDark = false, logoUrl, ...props }: Props) {
    return (
        <div {...props} className={`flex items-center gap-2 ${props.className}`}>
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 shadow-lg shadow-indigo-200 overflow-hidden">
                {logoUrl ? (
                    <img src={logoUrl} alt="Logo" className="h-full w-full object-contain" />
                ) : (
                    <span className="text-xl font-black text-white italic">A</span>
                )}
            </div>
            <div className="flex flex-col leading-none">
                <span className={`text-lg font-black tracking-tighter ${isDark ? 'text-white' : 'text-gray-800'}`}>ITSNU</span>
                <span className={`text-[10px] font-bold tracking-[0.3em] uppercase ${isDark ? 'text-indigo-300' : 'text-indigo-600'}`}>Akreditasi</span>
            </div>
        </div>
    );
}
