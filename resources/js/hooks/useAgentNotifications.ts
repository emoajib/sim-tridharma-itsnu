import { useState, useEffect, useCallback, useRef } from 'react';
import axios, { CancelTokenSource } from 'axios';

interface AgentNotification {
    id: number;
    agent: string;
    status: string;
    created_at: string;
    output_data?: Record<string, unknown>;
}

interface PredictionNotification {
    id: number;
    prodi_id: number;
    skor_prediksi: number;
    probabilitas_unggul: number;
    probabilitas_baik_sekali: number;
    created_at: string;
    prodi?: { nama_prodi: string };
}

interface WarningNotification {
    id: number;
    prodi_id: number;
    jenis_peringatan: string;
    tingkat: string;
    pesan: string;
    is_read: boolean;
    created_at: string;
    prodi?: { nama_prodi: string };
}

interface GenerationNotification {
    id: number;
    prodi_id: number;
    jenis_dokumen: string;
    status: string;
    created_at: string;
    prodi?: { nama_prodi: string };
}

interface UseAgentNotificationsReturn {
    notifications: AgentNotification[];
    predictions: PredictionNotification[];
    warnings: WarningNotification[];
    generations: GenerationNotification[];
    isPolling: boolean;
    startPolling: () => void;
    stopPolling: () => void;
    refresh: () => Promise<void>;
}

function mergeUniqueById<T extends { id: number }>(existing: T[], incoming: T[], maxItems = 10): T[] {
    const existingIds = new Set(existing.map(item => item.id));
    const newItems = incoming.filter(item => !existingIds.has(item.id));
    return [...newItems, ...existing].slice(0, maxItems);
}

export function useAgentNotifications(pollInterval = 10000): UseAgentNotificationsReturn {
    const [notifications, setNotifications] = useState<AgentNotification[]>([]);
    const [predictions, setPredictions] = useState<PredictionNotification[]>([]);
    const [warnings, setWarnings] = useState<WarningNotification[]>([]);
    const [generations, setGenerations] = useState<GenerationNotification[]>([]);
    const [isPolling, setIsPolling] = useState(false);

    const lastFetchRef = useRef<string | null>(null);
    const intervalRef = useRef<NodeJS.Timeout | null>(null);
    const cancelRef = useRef<CancelTokenSource | null>(null);
    const mountedRef = useRef(true);

    const fetchLatest = useCallback(async () => {
        if (cancelRef.current) {
            cancelRef.current.cancel('Request superseded by new fetch');
        }
        const source = axios.CancelToken.source();
        cancelRef.current = source;

        try {
            const params = lastFetchRef.current ? { after: lastFetchRef.current } : {};
            const response = await axios.get('/api/agents/latest', {
                params,
                cancelToken: source.token,
            });
            if (!mountedRef.current) return;

            const { logs, predictions: newPredictions, warnings: newWarnings, generations: newGenerations } = response.data;

            if (logs && logs.length > 0) {
                setNotifications(logs);
                const latestTime = logs[0]?.created_at;
                if (latestTime) lastFetchRef.current = latestTime;
            }

            if (newPredictions && newPredictions.length > 0) {
                setPredictions(prev => mergeUniqueById(prev, newPredictions));
            }

            if (newWarnings && newWarnings.length > 0) {
                setWarnings(newWarnings);
            }

            if (newGenerations && newGenerations.length > 0) {
                setGenerations(prev => mergeUniqueById(prev, newGenerations));
            }
        } catch (error) {
            if (!axios.isCancel(error)) {
                console.error('Failed to fetch agent notifications:', error);
            }
        }
    }, []);

    const startPolling = useCallback(() => {
        if (!isPolling) {
            setIsPolling(true);
            fetchLatest();
            intervalRef.current = setInterval(fetchLatest, pollInterval);
        }
    }, [isPolling, fetchLatest, pollInterval]);

    const stopPolling = useCallback(() => {
        if (intervalRef.current) {
            clearInterval(intervalRef.current);
            intervalRef.current = null;
        }
        if (cancelRef.current) {
            cancelRef.current.cancel('Polling stopped');
            cancelRef.current = null;
        }
        setIsPolling(false);
    }, []);

    const refresh = useCallback(async () => {
        await fetchLatest();
    }, [fetchLatest]);

    useEffect(() => {
        mountedRef.current = true;
        return () => {
            mountedRef.current = false;
            if (intervalRef.current) {
                clearInterval(intervalRef.current);
            }
            if (cancelRef.current) {
                cancelRef.current.cancel('Component unmounted');
            }
        };
    }, []);

    return {
        notifications,
        predictions,
        warnings,
        generations,
        isPolling,
        startPolling,
        stopPolling,
        refresh,
    };
}