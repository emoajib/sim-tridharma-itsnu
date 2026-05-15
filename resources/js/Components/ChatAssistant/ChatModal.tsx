import { useState, useRef, useEffect } from 'react';
import { router } from '@inertiajs/react';

interface Message {
    id: number;
    role: 'user' | 'assistant';
    content: string;
    timestamp: string;
}

interface Props {
    isOpen: boolean;
    onClose: () => void;
}

const defaultSuggestions = [
    'Apa kekurangan akreditasi prodi saya?',
    'Bagaimana cara meningkatkan skor C5?',
    'Berapa prediksi skor akreditasi prodi ini?',
    'Siapa dosen dengan publikasi terbaik?',
    'Berapa jumlah mahasiswa lulus tahun ini?',
];

export default function ChatModal({ isOpen, onClose }: Props) {
    const [messages, setMessages] = useState<Message[]>([
        {
            id: 1,
            role: 'assistant',
            content: 'Halo! Saya AI Assistant untuk Sistem Tridharma. Ada yang bisa saya bantu? Silakan pilih pertanyaan di bawah atau ketik sendiri.',
            timestamp: new Date().toISOString(),
        },
    ]);
    const [input, setInput] = useState('');
    const [loading, setLoading] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    function sendMessage() {
        if (!input.trim() || loading) return;

        const userMessage: Message = {
            id: Date.now(),
            role: 'user',
            content: input,
            timestamp: new Date().toISOString(),
        };

        setMessages((prev) => [...prev, userMessage]);
        setInput('');
        setLoading(true);

        // Simulate AI response (in production, this would call an API)
        setTimeout(() => {
            const assistantMessage: Message = {
                id: Date.now() + 1,
                role: 'assistant',
                content: generateResponse(input),
                timestamp: new Date().toISOString(),
            };
            setMessages((prev) => [...prev, assistantMessage]);
            setLoading(false);
        }, 1000);
    }

    function generateResponse(query: string): string {
        const lowerQuery = query.toLowerCase();
        
        if (lowerQuery.includes('kekurangan') || lowerQuery.includes('indikator')) {
            return 'Berdasarkan analisis terakhir, beberapa indikator yang perlu ditingkatkan:\n\n1. C5 (Sarana & Prasarana): Kekurangan 2 PC laboratorium\n2. C7 (Mahasiswa): Rasio dosen-mahasiswa perlu perbaikan\n\nSilakan akses halaman Peringatan untuk detail lengkap.';
        }
        
        if (lowerQuery.includes('prediksi') || lowerQuery.includes('skor')) {
            return 'Prediksi skor akreditasi saat ini: 3.45/4.00 dengan probabilitas UNGGUL sebesar 85%.\n\nGunakan Agent Prediksi di dashboard untuk simulasi lebih detail.';
        }
        
        if (lowerQuery.includes('publikasi') || lowerQuery.includes('dosen')) {
            return 'Dosen dengan publikasi terbaik:\n1. Dr. Ahmad - 12 Scopus\n2. Dr. Siti - 8 Scopus\n3. Dr. Budi - 5 Scopus\n\nAkses halaman Portofolio untuk detail lengkap.';
        }
        
        if (lowerQuery.includes('mahasiswa') || lowerQuery.includes('lulus')) {
            return 'Data mahasiswa tahun ini:\n- Total terdaftar: 450\n- Lulus: 120\n- Still aktif: 280\n\nAkses halaman Alumni untuk tracer study lengkap.';
        }

        return 'Terima kasih atas pertanyaan Anda. Untuk informasi detail, saya sarankan:\n\n• Akses halaman Dashboard untuk ringkasan\n• Gunakan menu Peringatan untuk lihat kekurangan\n• Jalankan Agent Rekomendasi untuk saran perbaikan\n• Cek halaman Verifikasi untuk status dokumen';
    }

    function handleSuggestion(suggestion: string) {
        setInput(suggestion);
    }

    function handleKeyPress(e: React.KeyboardEvent) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    }

    if (!isOpen) return null;

    return (
        <div className="fixed bottom-20 right-6 z-50 w-full max-w-md overflow-hidden rounded-lg bg-white shadow-2xl">
            {/* Header */}
            <div className="flex items-center justify-between bg-indigo-600 p-4">
                <div className="flex items-center gap-2">
                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-white">
                        <svg className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <h3 className="text-sm font-semibold text-white">AI Assistant</h3>
                        <p className="text-xs text-indigo-200">Sistem Tridharma</p>
                    </div>
                </div>
                <button
                    onClick={onClose}
                    className="text-indigo-200 hover:text-white"
                >
                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {/* Messages */}
            <div className="h-80 overflow-y-auto p-4">
                {messages.map((msg) => (
                    <div
                        key={msg.id}
                        className={`mb-3 flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}
                    >
                        <div
                            className={`max-w-[80%] rounded-lg p-3 text-sm ${
                                msg.role === 'user'
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-100 text-gray-800'
                            }`}
                        >
                            <p className="whitespace-pre-wrap">{msg.content}</p>
                            <p className={`mt-1 text-[10px] ${
                                msg.role === 'user' ? 'text-indigo-200' : 'text-gray-400'
                            }`}>
                                {new Date(msg.timestamp).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                            </p>
                        </div>
                    </div>
                ))}
                {loading && (
                    <div className="mb-3 flex justify-start">
                        <div className="max-w-[80%] rounded-lg bg-gray-100 p-3">
                            <div className="flex gap-1">
                                <div className="h-2 w-2 animate-bounce rounded-full bg-gray-400"></div>
                                <div className="h-2 w-2 animate-bounce rounded-full bg-gray-400" style={{ animationDelay: '0.1s' }}></div>
                                <div className="h-2 w-2 animate-bounce rounded-full bg-gray-400" style={{ animationDelay: '0.2s' }}></div>
                            </div>
                        </div>
                    </div>
                )}
                <div ref={messagesEndRef} />
            </div>

            {/* Suggestions */}
            <div className="border-t bg-gray-50 p-3">
                <p className="mb-2 text-xs text-gray-500">Pertanyaan cepat:</p>
                <div className="flex flex-wrap gap-2">
                    {defaultSuggestions.slice(0, 3).map((suggestion, idx) => (
                        <button
                            key={idx}
                            onClick={() => handleSuggestion(suggestion)}
                            className="rounded-full border border-gray-200 bg-white px-3 py-1 text-xs text-gray-600 hover:border-indigo-300 hover:text-indigo-600"
                        >
                            {suggestion.length > 30 ? suggestion.substring(0, 30) + '...' : suggestion}
                        </button>
                    ))}
                </div>
            </div>

            {/* Input */}
            <div className="border-t p-3">
                <div className="flex gap-2">
                    <input
                        type="text"
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        onKeyPress={handleKeyPress}
                        placeholder="Ketik pertanyaan..."
                        className="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none"
                    />
                    <button
                        onClick={sendMessage}
                        disabled={!input.trim() || loading}
                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                    >
                        Kirim
                    </button>
                </div>
            </div>
        </div>
    );
}