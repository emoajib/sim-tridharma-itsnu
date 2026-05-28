import { describe, it, expect, beforeEach, vi } from 'vitest';
import axios from 'axios';
import { useKurikulumStore } from '../kurikulumStore';

vi.mock('axios');
const mockedAxios = vi.mocked(axios);

interface Kurikulum {
    id: number;
    nama: string;
    tahun: number;
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
            { id: 1, nama: 'Kurikulum 2020', tahun: 2020 },
            { id: 2, nama: 'Kurikulum 2024', tahun: 2024 },
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
        const newItem = { id: 3, nama: 'Kurikulum 2025', tahun: 2025 };

        mockedAxios.post.mockResolvedValueOnce({
            data: { data: newItem },
        });

        const result = await useKurikulumStore
            .getState()
            .create({ nama: 'Kurikulum 2025', tahun: 2025 });

        expect(result).toEqual(newItem);
        expect(mockedAxios.post).toHaveBeenCalledWith('/api/kurikulum', {
            nama: 'Kurikulum 2025',
            tahun: 2025,
        });
    });

    it('should update an existing kurikulum', async () => {
        useKurikulumStore.setState({
            items: [{ id: 1, nama: 'Kurikulum Lama', tahun: 2019 }],
        });

        const updatedItem = { id: 1, nama: 'Kurikulum Update', tahun: 2025 };

        mockedAxios.put.mockResolvedValueOnce({
            data: { data: updatedItem },
        });

        const result = await useKurikulumStore
            .getState()
            .update(1, { nama: 'Kurikulum Update', tahun: 2025 });

        expect(result).toEqual(updatedItem);
        expect(mockedAxios.put).toHaveBeenCalledWith('/api/kurikulum/1', {
            nama: 'Kurikulum Update',
            tahun: 2025,
        });

        const state = useKurikulumStore.getState();
        expect(state.items[0]).toEqual(updatedItem);
    });

    it('should delete a kurikulum', async () => {
        useKurikulumStore.setState({
            items: [
                { id: 1, nama: 'Kurikulum A', tahun: 2020 },
                { id: 2, nama: 'Kurikulum B', tahun: 2024 },
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
