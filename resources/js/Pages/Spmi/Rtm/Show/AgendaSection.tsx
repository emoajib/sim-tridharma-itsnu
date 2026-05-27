interface Props {
    agenda: string | null;
}

export default function AgendaSection({ agenda }: Props) {
    return (
        <div className="p-6">
            {agenda ? (
                <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{agenda}</p>
            ) : (
                <p className="text-sm italic text-gray-400">Belum ada agenda.</p>
            )}
        </div>
    );
}
