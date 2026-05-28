import { describe, it, expect, beforeEach, vi } from 'vitest';
import axios from 'axios';
import { useProdiStore } from '../prodiStore';

vi.mock('axios');
const mockedAxios = vi.mocked(axios);

interface Prodi {
    id: number;
    nama_prodi: string;
    kode_prodi: string;
}

describe('useProdiStore', () => {
    beforeEach(() => {
        useProdiStore.getState().reset();
        vi.clearAllMocks();
    });

    it('should create store with initial state', () => {
        const state = useProdiStore.getState();
        expect(state.items).toEqual([]);
        expect(state.loading).toBe(false);
        expect(state.error).toBeNull();
    });

    it('should fetch prodi from API', async () => {
        const mockData: Prodi[] = [
            { id: 1, nama_prodi: 'Teknik Informatika', kode_prodi: 'TI' },
            { id: 2, nama_prodi: 'Sistem Informasi', kode_prodi: 'SI' },
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

        await useProdiStore.getState().fetch({ page: 1, per_page: 10 });

        const state = useProdiStore.getState();
        expect(state.items).toEqual(mockData);
        expect(state.error).toBeNull();
        expect(state.pagination.total).toBe(2);
    });

    it('should create a new prodi', async () => {
        const newItem: Prodi = {
            id: 3,
            nama_prodi: 'Ilmu Komputer',
            kode_prodi: 'IK',
        };

        mockedAxios.post.mockResolvedValueOnce({
            data: { data: newItem },
        });

        const result = await useProdiStore
            .getState()
            .create({ nama_prodi: 'Ilmu Komputer', kode_prodi: 'IK' });

        expect(result).toEqual(newItem);
        expect(mockedAxios.post).toHaveBeenCalledWith('/api/prodi', {
            nama_prodi: 'Ilmu Komputer',
            kode_prodi: 'IK',
        });
    });

    it('should update an existing prodi', async () => {
        useProdiStore.setState({
            items: [
                {
                    id: 1,
                    nama_prodi: 'Prodi Lama',
                    kode_prodi: 'PL',
                },
            ],
        });

        const updatedItem: Prodi = {
            id: 1,
            nama_prodi: 'Prodi Update',
            kode_prodi: 'PU',
        };

        mockedAxios.put.mockResolvedValueOnce({
            data: { data: updatedItem },
        });

        const result = await useProdiStore
            .getState()
            .update(1, { nama_prodi: 'Prodi Update' });

        expect(result).toEqual(updatedItem);
        expect(mockedAxios.put).toHaveBeenCalledWith('/api/prodi/1', {
            nama_prodi: 'Prodi Update',
        });
    });

    it('should delete a prodi', async () => {
        useProdiStore.setState({
            items: [
                { id: 1, nama_prodi: 'Prodi A', kode_prodi: 'PA' },
                { id: 2, nama_prodi: 'Prodi B', kode_prodi: 'PB' },
            ],
        });

        mockedAxios.delete.mockResolvedValueOnce({});

        await useProdiStore.getState().remove(2);

        expect(mockedAxios.delete).toHaveBeenCalledWith('/api/prodi/2');

        const state = useProdiStore.getState();
        expect(state.items).toHaveLength(1);
        expect(state.items[0].id).toBe(1);
    });

    it('should handle API error with response message', async () => {
        mockedAxios.get.mockRejectedValueOnce({
            response: { data: { message: 'Prodi not found' } },
        });

        await useProdiStore.getState().fetch();

        const state = useProdiStore.getState();
        expect(state.error).toBe('Prodi not found');
        expect(state.loading).toBe(false);
    });
});
