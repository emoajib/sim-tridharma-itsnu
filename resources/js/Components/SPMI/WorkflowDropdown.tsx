import { useState, useRef, useEffect } from 'react';
import { ChevronDown } from 'lucide-react';
import StatusBadge from './StatusBadge';

interface WorkflowDropdownProps {
    currentStatus: string;
    workflowType: 'audit' | 'capa';
    onTransition: (toStatus: string) => void;
    transitions: string[];
    disabled?: boolean;
}

function capitalizeLabel(status: string): string {
    return status
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

export default function WorkflowDropdown({
    currentStatus,
    workflowType,
    onTransition,
    transitions,
    disabled = false,
}: WorkflowDropdownProps) {
    const [open, setOpen] = useState(false);
    const dropdownRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        function handleClickOutside(event: MouseEvent) {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target as Node)) {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return (
        <div ref={dropdownRef} className="relative inline-block">
            <button
                type="button"
                onClick={() => !disabled && setOpen(!open)}
                disabled={disabled}
                className={`inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-medium transition-all ${
                    disabled
                        ? 'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-400'
                        : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400'
                }`}
            >
                <StatusBadge status={currentStatus} workflowType={workflowType} size="sm" />
                {!disabled && <ChevronDown className="h-3.5 w-3.5 text-gray-500" />}
            </button>

            {open && transitions.length > 0 && (
                <div className="absolute right-0 z-50 mt-1 min-w-[180px] rounded-lg border border-gray-200 bg-white py-1 shadow-lg">
                    <div className="border-b border-gray-100 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                        Ubah Status
                    </div>
                    {transitions.map((toStatus) => (
                        <button
                            key={toStatus}
                            type="button"
                            onClick={() => {
                                onTransition(toStatus);
                                setOpen(false);
                            }}
                            className="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700"
                        >
                            <StatusBadge status={toStatus} workflowType={workflowType} size="sm" />
                            <span>{capitalizeLabel(toStatus)}</span>
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
