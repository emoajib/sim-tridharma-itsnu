import { useState, useRef, useEffect } from 'react';

interface Message {
    id: number;
    role: 'user' | 'assistant';
    content: string;
    sources?: { judul: string; sumber: string; skor: number }[];
    timestamp: string;
}

interface Props {
    isOpen: boolean;
    onClose: () => void;
}

const defaultSuggestions = [
    'Apa itu akreditasi dan bagaimana prosesnya?',
    'Apa saja dokumen yang dibutuhkan untuk akreditasi program studi?',
    'Bagaimana cara menghitung skor akreditasi?',
    'Apa standar akreditasi perguruan tinggi?',
    'Jelaskan tentang indikator penilaian akreditasi',
];

export default function ChatModal({ isOpen, onClose }: Props) {
    const [messages, setMessages] = useState<Message[]>([
        {
            id: 1,
            role: 'assistant',
            content: 'Halo! Saya AI Assistant dengan RAG Knowledge Base. Saya bisa menjawab pertanyaan berdasarkan dokumen kebijakan akreditasi yang telah diupload. Coba tanya tentang persyaratan akreditasi atau pilih pertanyaan di bawah.',
            timestamp: new Date().toISOString(),
        },
    ]);
    const [input, setInput] = useState('');
    const [loading, setLoading] = useState(false);
    const messagesEndRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    }, [messages]);

    async function sendMessage() {
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

        try {
            const response = await fetch('/api/rag/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ question: input }),
            });

            if (!response.ok) throw new Error('API error');

            const data = await response.json();

            let content = data.answer || 'Maaf, tidak dapat memproses pertanyaan.';

            if (data.sources && data.sources.length > 0) {
                const sourcesList = data.sources
                    .map((s: any) => `📄 ${s.judul} (${s.skor}%)`)
                    .join('\n');
                content += '\n\n---\nSumber:\n' + sourcesList;
            }

            const assistantMessage: Message = {
                id: Date.now() + 1,
                role: 'assistant',
                content: content,
                sources: data.sources,
                timestamp: new Date().toISOString(),
            };
            setMessages((prev) => [...prev, assistantMessage]);
        } catch {
            const fallbackMessage: Message = {
                id: Date.now() + 1,
                role: 'assistant',
                content: 'Maaf, AI Service sedang tidak tersedia. Pastikan Python AI Service berjalan di port 5001.\n\n`cd ai-service && python main.py`',
                timestamp: new Date().toISOString(),
            };
            setMessages((prev) => [...prev, fallbackMessage]);
        } finally {
            setLoading(false);
        }
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
            <div className="flex items-center justify-between bg-indigo-600 p-4">
                <div className="flex items-center gap-2">
                    <div className="flex h-8 w-8 items-center justify-center rounded-full bg-white">
                        <svg className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <h3 className="text-sm font-semibold text-white">AI Assistant</h3>
                        <p className="text-xs text-indigo-200">RAG Knowledge Base</p>
                    </div>
                </div>
                <button onClick={onClose} className="text-indigo-200 hover:text-white">
                    <svg className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div className="h-80 overflow-y-auto p-4">
                {messages.map((msg) => (
                    <div key={msg.id} className={`mb-3 flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}>
                        <div className={`max-w-[80%] rounded-lg p-3 text-sm ${
                            msg.role === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800'
                        }`}>
                            <p className="whitespace-pre-wrap">{msg.content}</p>
                            {msg.sources && msg.sources.length > 0 && (
                                <div className="mt-2 pt-2 border-t border-gray-200">
                                    <p className="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Sumber:</p>
                                    {msg.sources.map((s, i) => (
                                        <p key={i} className="text-[10px] text-gray-500">
                                            📄 {s.judul} — {s.skor}%
                                        </p>
                                    ))}
                                </div>
                            )}
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
                            <p className="text-[10px] text-gray-400 mt-1">Mencari jawaban dari dokumen...</p>
                        </div>
                    </div>
                )}
                <div ref={messagesEndRef} />
            </div>

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

            <div className="border-t p-3">
                <div className="flex gap-2">
                    <input
                        type="text"
                        value={input}
                        onChange={(e) => setInput(e.target.value)}
                        onKeyPress={handleKeyPress}
                        placeholder="Tanya tentang akreditasi..."
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
