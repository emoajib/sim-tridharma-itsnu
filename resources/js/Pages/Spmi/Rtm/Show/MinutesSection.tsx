interface Props {
    notulen: string | null;
}

export default function MinutesSection({ notulen }: Props) {
    return (
        <div className="p-6">
            {notulen ? (
                <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-700">{notulen}</p>
            ) : (
                <p className="text-sm italic text-gray-400">Belum ada notulen.</p>
            )}
        </div>
    );
}
