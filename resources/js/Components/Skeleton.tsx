interface SkeletonProps {
    className?: string;
    variant?: 'text' | 'circular' | 'rectangular';
    width?: string | number;
    height?: string | number;
}

export function Skeleton({ 
    className = '', 
    variant = 'rectangular', 
    width, 
    height 
}: SkeletonProps) {
    const baseClasses = 'animate-pulse bg-gray-200';
    
    const variantClasses = {
        text: 'rounded',
        circular: 'rounded-full',
        rectangular: 'rounded-md',
    };

    return (
        <div
            className={`${baseClasses} ${variantClasses[variant]} ${className}`}
            style={{
                width: width,
                height: height,
            }}
        />
    );
}

export function SkeletonCard({ className = '' }: { className?: string }) {
    return (
        <div className={`bg-white rounded-lg shadow p-4 ${className}`}>
            <Skeleton variant="text" width="60%" height={16} className="mb-2" />
            <Skeleton variant="text" width="40%" height={24} />
        </div>
    );
}

export function SkeletonTable({ rows = 5, cols = 4 }: { rows?: number; cols?: number }) {
    return (
        <div className="overflow-hidden">
            <div className="bg-gray-50 border-b border-gray-200">
                <div className="flex">
                    {Array.from({ length: cols }).map((_, i) => (
                        <div key={i} className="flex-1 p-4">
                            <Skeleton variant="text" width="80%" />
                        </div>
                    ))}
                </div>
            </div>
            {Array.from({ length: rows }).map((_, rowIndex) => (
                <div key={rowIndex} className="border-b border-gray-200 flex">
                    {Array.from({ length: cols }).map((_, colIndex) => (
                        <div key={colIndex} className="flex-1 p-4">
                            <Skeleton variant="text" width={Math.random() * 40 + 60 + '%'} />
                        </div>
                    ))}
                </div>
            ))}
        </div>
    );
}

export function SkeletonChart({ className = '' }: { className?: string }) {
    return (
        <div className={`bg-white rounded-lg shadow p-4 ${className}`}>
            <Skeleton variant="text" width="40%" height={20} className="mb-4" />
            <Skeleton variant="rectangular" width="100%" height={200} />
        </div>
    );
}

export function SkeletonList({ items = 3 }: { items?: number }) {
    return (
        <div className="space-y-3">
            {Array.from({ length: items }).map((_, i) => (
                <div key={i} className="flex items-center gap-3 p-3 bg-white rounded-lg shadow">
                    <Skeleton variant="circular" width={40} height={40} />
                    <div className="flex-1">
                        <Skeleton variant="text" width="70%" height={14} className="mb-1" />
                        <Skeleton variant="text" width="40%" height={12} />
                    </div>
                </div>
            ))}
        </div>
    );
}