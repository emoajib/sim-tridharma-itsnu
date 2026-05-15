import { useState } from 'react';

interface Props {
    onClick?: () => void;
}

export default function ChatButton({ onClick }: Props) {
    const [isHovered, setIsHovered] = useState(false);

    return (
        <div className="fixed bottom-6 right-6 z-40">
            {/* Tooltip */}
            {isHovered && (
                <div className="absolute bottom-14 right-0 mb-2 w-48 rounded-lg bg-gray-800 p-3 text-sm text-white shadow-lg">
                    <p>Tanya AI Assistant untuk info akreditasi prodi Anda</p>
                </div>
            )}

            {/* Chat Button */}
            <button
                onClick={onClick}
                onMouseEnter={() => setIsHovered(true)}
                onMouseLeave={() => setIsHovered(false)}
                className="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg transition hover:bg-indigo-700 hover:scale-110"
            >
                <svg className="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                </svg>
            </button>

            {/* Notification Badge */}
            <span className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white animate-pulse">
                AI
            </span>
        </div>
    );
}