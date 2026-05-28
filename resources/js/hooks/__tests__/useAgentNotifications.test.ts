import { describe, it, expect, vi, beforeEach } from 'vitest';
import { renderHook, act, waitFor } from '@testing-library/react';
import { useAgentNotifications } from '../useAgentNotifications';

const mockCancelTokenSource = {
    token: 'mock-cancel-token',
    cancel: vi.fn(),
};

vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
        CancelToken: {
            source: vi.fn(() => mockCancelTokenSource),
        },
        isCancel: vi.fn(() => false),
    },
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
    CancelToken: {
        source: vi.fn(() => mockCancelTokenSource),
    },
    isCancel: vi.fn(() => false),
}));

import axios from 'axios';
const mockedAxios = vi.mocked(axios);

describe('useAgentNotifications', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('should return initial empty state', () => {
        const { result } = renderHook(() => useAgentNotifications());

        expect(result.current.notifications).toEqual([]);
        expect(result.current.predictions).toEqual([]);
        expect(result.current.warnings).toEqual([]);
        expect(result.current.generations).toEqual([]);
        expect(result.current.isPolling).toBe(false);
    });

    it('should fetch notifications when startPolling is called', async () => {
        const mockResponse = {
            data: {
                logs: [
                    {
                        id: 1,
                        agent: 'analisis-akreditasi',
                        status: 'completed',
                        created_at: '2024-01-15T10:00:00Z',
                    },
                ],
                predictions: [],
                warnings: [],
                generations: [],
            },
        };

        mockedAxios.get.mockResolvedValueOnce(mockResponse);

        const { result } = renderHook(() => useAgentNotifications());

        act(() => {
            result.current.startPolling();
        });

        // Flush microtasks to resolve the axios promise
        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 0));
        });

        expect(result.current.notifications).toHaveLength(1);
        expect(result.current.notifications[0].agent).toBe(
            'analisis-akreditasi',
        );
        expect(result.current.isPolling).toBe(true);
    });

    it('should handle fetch errors gracefully', async () => {
        mockedAxios.get.mockRejectedValueOnce(new Error('Network error'));

        const { result } = renderHook(() => useAgentNotifications());

        act(() => {
            result.current.startPolling();
        });

        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 0));
        });

        expect(result.current.notifications).toEqual([]);
        expect(result.current.isPolling).toBe(true);
    });

    it('should stop polling when stopPolling is called', async () => {
        mockedAxios.get.mockResolvedValue({
            data: { logs: [], predictions: [], warnings: [], generations: [] },
        });

        const { result } = renderHook(() => useAgentNotifications());

        act(() => {
            result.current.startPolling();
        });

        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 0));
        });

        expect(result.current.isPolling).toBe(true);

        act(() => {
            result.current.stopPolling();
        });

        expect(result.current.isPolling).toBe(false);
    });

    it('should refresh notifications when refresh is called', async () => {
        const mockResponse = {
            data: {
                logs: [
                    {
                        id: 2,
                        agent: 'prediksi-kelulusan',
                        status: 'running',
                        created_at: '2024-01-16T10:00:00Z',
                    },
                ],
                predictions: [],
                warnings: [],
                generations: [],
            },
        };

        mockedAxios.get.mockResolvedValueOnce(mockResponse);

        const { result } = renderHook(() => useAgentNotifications());

        await act(async () => {
            await result.current.refresh();
        });

        expect(mockedAxios.get).toHaveBeenCalledTimes(1);
        expect(result.current.notifications).toHaveLength(1);
        expect(result.current.notifications[0].agent).toBe(
            'prediksi-kelulusan',
        );
    });

    it('should call API with correct endpoint and params', async () => {
        mockedAxios.get.mockResolvedValue({
            data: { logs: [], predictions: [], warnings: [], generations: [] },
        });

        const { result } = renderHook(() => useAgentNotifications());

        act(() => {
            result.current.startPolling();
        });

        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 0));
        });

        expect(mockedAxios.get).toHaveBeenCalledWith(
            '/api/agents/latest',
            expect.objectContaining({
                params: {},
                cancelToken: 'mock-cancel-token',
            }),
        );
    });

    it('should set warnings when returned from API', async () => {
        const mockResponse = {
            data: {
                logs: [],
                predictions: [],
                warnings: [
                    {
                        id: 1,
                        prodi_id: 1,
                        jenis_peringatan: 'akreditasi',
                        tingkat: 'kritis',
                        pesan: 'Nilai turun',
                        is_read: false,
                        created_at: '2024-01-15T10:00:00Z',
                    },
                ],
                generations: [],
            },
        };

        mockedAxios.get.mockResolvedValueOnce(mockResponse);

        const { result } = renderHook(() => useAgentNotifications());

        act(() => {
            result.current.startPolling();
        });

        await act(async () => {
            await new Promise((resolve) => setTimeout(resolve, 0));
        });

        expect(result.current.warnings).toHaveLength(1);
        expect(result.current.warnings[0].pesan).toBe('Nilai turun');
    });
});
