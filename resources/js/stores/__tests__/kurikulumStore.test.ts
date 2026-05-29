import { describe, it, expect, beforeEach, vi } from 'vitest';
import axios from 'axios';
import { useKurikulumStore } from '../kurikulumStore';

vi.mock('axios');
const mockedAxios = vi.mocked(axios);

interface Kurikulum {
    id: number;
    nama_kurikulum: string;
    tahun_berlaku: string;
    prodi_id: number;
    is_active: boolean;
}

describe('useKurikulumStore', () => {
    beforeEach(() => {
        useKurikulumStore.getState().reset();
        vi.clearAllMocks();
    });

    it('should create store with initial state', () => {
        const state = useKurikulumStore.getState();
        expect(state.items).toEqual([]);
        expect(state.loading).toBe(false);
        expect(state.error).toBeNull();
        expect(state.selectedItem).toBeNull();
    });

    it('should fetch kurikulum from API', async () => {
        const mockData: Kurikulum[] = [
            { id: 1, nama_kurikulum: 'Kurikulum 2020', tahun_berlaku: '2020', prodi_id: 1, is_active: true },
            { id: 2, nama_kurikulum: 'Kurikulum 2024', tahun_berlaku: '2024', prodi_id: 2, is_active: true },
        ];

        mockedAxios.get.mockResolvedValueOnce({
            data: {
                data: mockData,
                current_page: 1,
                last_page: 1,
                total: 2,
                per_page: 10,
                from: 1,
                to: 2,
                links: [],
            },
        });

        await useKurikulumStore.getState().fetch();

        const state = useKurikulumStore.getState();
        expect(state.items).toEqual(mockData);
        expect(state.error).toBeNull();
    });

    it('should create a new kurikulum', async () => {
        const newItem = { id: 3, nama_kurikulum: 'Kurikulum 2025', tahun_berlaku: '2025', prodi_id: 1, is_active: true };

        mockedAxios.post.mockResolvedValueOnce({
            data: { data: newItem },
        });

        const result = await useKurikulumStore
            .getState()
            .create({ nama_kurikulum: 'Kurikulum 2025', tahun_berlaku: '2025', prodi_id: 1, is_active: true });

        expect(result).toEqual(newItem);
        expect(mockedAxios.post).toHaveBeenCalledWith('/api/kurikulum', {
            nama_kurikulum: 'Kurikulum 2025',
            tahun_berlaku: '2025',
            prodi_id: 1,
            is_active: true,
        });
    });

    it('should update an existing kurikulum', async () => {
        useKurikulumStore.setState({
            items: [{ id: 1, nama_kurikulum: 'Kurikulum Lama', tahun_berlaku: '2019', prodi_id: 1, is_active: true }],
        });

        const updatedItem = { id: 1, nama_kurikulum: 'Kurikulum Update', tahun_berlaku: '2025', prodi_id: 1, is_active: true };

        mockedAxios.put.mockResolvedValueOnce({
            data: { data: updatedItem },
        });

        const result = await useKurikulumStore
            .getState()
            .update(1, { nama_kurikulum: 'Kurikulum Update', tahun_berlaku: '2025' });

        expect(result).toEqual(updatedItem);
        expect(mockedAxios.put).toHaveBeenCalledWith('/api/kurikulum/1', {
            nama_kurikulum: 'Kurikulum Update',
            tahun_berlaku: '2025',
            prodi_id: 1,
            is_active: true,
        });

        const state = useKurikulumStore.getState();
        expect(state.items[0]).toEqual(updatedItem);
    });

    it('should delete a kurikulum', async () => {
        useKurikulumStore.setState({
            items: [
                { id: 1, nama_kurikulum: 'Kurikulum A', tahun_berlaku: '2020', prodi_id: 1, is_active: true },
                { id: 2, nama_kurikulum: 'Kurikulum B', tahun_berlaku: '2024', prodi_id: 2, is_active: true },
            ],
        });

        mockedAxios.delete.mockResolvedValueOnce({});

        await useKurikulumStore.getState().remove(1);

        expect(mockedAxios.delete).toHaveBeenCalledWith('/api/kurikulum/1');

        const state = useKurikulumStore.getState();
        expect(state.items).toHaveLength(1);
        expect(state.items[0].id).toBe(2);
    });

    it('should handle fetch errors', async () => {
        mockedAxios.get.mockRejectedValueOnce(
            new Error('Failed to fetch kurikulum'),
        );

        await useKurikulumStore.getState().fetch();

        const state = useKurikulumStore.getState();
        expect(state.loading).toBe(false);
        expect(state.error).toBe('Failed to fetch kurikulum');
    });
});
