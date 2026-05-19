import { PieChart, Pie, Cell, ResponsiveContainer } from 'recharts';

interface Props {
    value: number;
    max?: number;
    label?: string;
    predicate?: string;
    className?: string;
}

const PREDICATE_COLORS: Record<string, string> = {
    'UNGGUL': '#16a34a',
    'BAIK SEKALI': '#2563eb',
    'BAIK': '#f59e0b',
    'TIDAK': '#dc2626',
};

export default function GaugeChart({ 
    value = 0, 
    max = 4, 
    label = 'Skor Prediksi',
    predicate,
    className = '' 
}: Props) {
    const percentage = (value / max) * 100;
    const data = [
        { name: 'Value', value: percentage },
        { name: 'Remaining', value: 100 - percentage },
    ];
    
    const color = predicate ? PREDICATE_COLORS[predicate] || '#6366f1' : '#6366f1';

    return (
        <div className={`bg-white rounded-lg p-4 shadow-sm ${className}`}>
            <div className="mb-4 flex items-center gap-2">
                <svg className="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 className="font-semibold text-gray-900">{label}</h3>
            </div>

            <div className="relative h-40 flex items-center justify-center">
                <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                        <Pie
                            data={data}
                            cx="50%"
                            cy="70%"
                            startAngle={180}
                            endAngle={0}
                            innerRadius={60}
                            outerRadius={80}
                            paddingAngle={0}
                            dataKey="value"
                        >
                            <Cell fill={color} />
                            <Cell fill="#e5e7eb" />
                        </Pie>
                    </PieChart>
                </ResponsiveContainer>
                <div className="absolute inset-0 flex flex-col items-center justify-center pt-8">
                    <div className="text-3xl font-bold text-gray-900">
                        {value.toFixed(2)}
                    </div>
                    <div className="text-sm text-gray-500">/ {max}</div>
                </div>
            </div>

            {predicate && (
                <div className="mt-2 text-center">
                    <span 
                        className="px-3 py-1 text-sm font-semibold rounded-full"
                        style={{ 
                            backgroundColor: `${color}20`,
                            color: color 
                        }}
                    >
                        {predicate}
                    </span>
                </div>
            )}
        </div>
    );
}