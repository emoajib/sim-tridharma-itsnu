import { useState, useEffect, useCallback } from 'react';
import axios from 'axios';

interface AgentNotification {
    id: number;
    agent: string;
    status: string;
    created_at: string;
    output_data?: any;
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

export function useAgentNotifications(pollInterval = 10000): UseAgentNotificationsReturn {
    const [notifications, setNotifications] = useState<AgentNotification[]>([]);
    const [predictions, setPredictions] = useState<PredictionNotification[]>([]);
    const [warnings, setWarnings] = useState<WarningNotification[]>([]);
    const [generations, setGenerations] = useState<GenerationNotification[]>([]);
    const [isPolling, setIsPolling] = useState(false);
    const [lastFetch, setLastFetch] = useState<string | null>(null);
    const [intervalId, setIntervalId] = useState<NodeJS.Timeout | null>(null);

    const fetchLatest = useCallback(async () => {
        try {
            const params = lastFetch ? { after: lastFetch } : {};
            const response = await axios.get('/api/agents/latest', { params });
            const { logs, predictions: newPredictions, warnings: newWarnings, generations: newGenerations } = response.data;

            if (logs && logs.length > 0) {
                setNotifications(logs);
                const latestTime = logs[0]?.created_at;
                if (latestTime) setLastFetch(latestTime);
            }

            if (newPredictions && newPredictions.length > 0) {
                setPredictions(prev => {
                    const existingIds = new Set(prev.map((p: PredictionNotification) => p.id));
                    const newItems = newPredictions.filter((p: PredictionNotification) => !existingIds.has(p.id));
                    return [...newItems, ...prev].slice(0, 10);
                });
            }

            if (newWarnings && newWarnings.length > 0) {
                setWarnings(newWarnings);
            }

            if (newGenerations && newGenerations.length > 0) {
                setGenerations(prev => {
                    const existingIds = new Set(prev.map((g: GenerationNotification) => g.id));
                    const newItems = newGenerations.filter((g: GenerationNotification) => !existingIds.has(g.id));
                    return [...newItems, ...prev].slice(0, 10);
                });
            }
        } catch (error) {
            console.error('Failed to fetch agent notifications:', error);
        }
    }, [lastFetch]);

    const startPolling = useCallback(() => {
        if (!isPolling) {
            setIsPolling(true);
            fetchLatest();
            const id = setInterval(fetchLatest, pollInterval);
            setIntervalId(id);
        }
    }, [isPolling, fetchLatest, pollInterval]);

    const stopPolling = useCallback(() => {
        if (isPolling && intervalId) {
            clearInterval(intervalId);
            setIntervalId(null);
            setIsPolling(false);
        }
    }, [isPolling, intervalId]);

    const refresh = useCallback(async () => {
        await fetchLatest();
    }, [fetchLatest]);

    useEffect(() => {
        return () => {
            if (intervalId) {
                clearInterval(intervalId);
            }
        };
    }, [intervalId]);

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