import { describe, it, expect, beforeEach, vi } from 'vitest';
import axios from 'axios';
import { useMitraStore } from '../mitraStore';

vi.mock('axios');
const mockedAxios = vi.mocked(axios);

interface Mitra {
    id: number;
    nama_mitra: string;
    jenis_mitra: string;
    alamat?: string;
    kontak?: string;
    telepon?: string;
    email?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

describe('useMitraStore', () => {
    beforeEach(() => {
        useMitraStore.getState().reset();
        vi.clearAllMocks();
    });

    it('should create store with initial state', () => {
        const state = useMitraStore.getState();
        expect(state.items).toEqual([]);
        expect(state.loading).toBe(false);
        expect(state.error).toBeNull();
        expect(state.selectedItem).toBeNull();
        expect(state.pagination).toEqual({
            currentPage: 1,
            lastPage: 1,
            total: 0,
            perPage: 10,
        });
    });

    it('should fetch mitra from API', async () => {
        const mockData: Mitra[] = [
            { id: 1, nama_mitra: 'PT Maju Jaya', jenis_mitra: 'industri', alamat: '', kontak: '', telepon: '', email: '', is_active: true, created_at: '', updated_at: '' },
            { id: 2, nama_mitra: 'Universitas Mitra', jenis_mitra: 'pendidikan', alamat: '', kontak: '', telepon: '', email: '', is_active: true, created_at: '', updated_at: '' },
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

        await useMitraStore.getState().fetch();

        const state = useMitraStore.getState();
        expect(state.items).toEqual(mockData);
        expect(state.loading).toBe(false);
        expect(state.error).toBeNull();
    });

    it('should create a new mitra', async () => {
        const newItem: Mitra = {
            id: 3,
            nama_mitra: 'PT Kerjasama',
            jenis_mitra: 'industri',
            alamat: '',
            kontak: '',
            telepon: '',
            email: '',
            is_active: true,
            created_at: '',
            updated_at: '',
        };

        mockedAxios.post.mockResolvedValueOnce({
            data: { data: newItem },
        });

        const result = await useMitraStore
            .getState()
            .create({ nama_mitra: 'PT Kerjasama', jenis_mitra: 'industri' });

        expect(result).toEqual(newItem);
        expect(mockedAxios.post).toHaveBeenCalledWith('/api/mitra', {
            nama_mitra: 'PT Kerjasama',
            jenis_mitra: 'industri',
        });

        const state = useMitraStore.getState();
        expect(state.items).toContainEqual(newItem);
    });

    it('should update an existing mitra', async () => {
        useMitraStore.setState({
            items: [
                { id: 1, nama_mitra: 'Mitra Lama', jenis_mitra: 'pendidikan', alamat: '', kontak: '', telepon: '', email: '', is_active: true, created_at: '', updated_at: '' },
            ],
        });

        const updatedItem: Mitra = {
            id: 1,
            nama_mitra: 'Mitra Update',
            jenis_mitra: 'industri',
            alamat: '',
            kontak: '',
            telepon: '',
            email: '',
            is_active: true,
            created_at: '',
            updated_at: '',
        };

        mockedAxios.put.mockResolvedValueOnce({
            data: { data: updatedItem },
        });

        const result = await useMitraStore
            .getState()
            .update(1, { nama_mitra: 'Mitra Update', jenis_mitra: 'industri' });

        expect(result).toEqual(updatedItem);
        expect(mockedAxios.put).toHaveBeenCalledWith('/api/mitra/1', {
            nama_mitra: 'Mitra Update',
            jenis_mitra: 'industri',
            alamat: '',
            kontak: '',
            telepon: '',
            email: '',
            is_active: true,
            created_at: '',
            updated_at: '',
        });

        const state = useMitraStore.getState();
        expect(state.items[0]).toEqual(updatedItem);
    });

    it('should delete a mitra', async () => {
        useMitraStore.setState({
            items: [
                { id: 1, nama_mitra: 'Mitra A', jenis_mitra: 'industri', alamat: '', kontak: '', telepon: '', email: '', is_active: true, created_at: '', updated_at: '' },
                { id: 2, nama_mitra: 'Mitra B', jenis_mitra: 'pendidikan', alamat: '', kontak: '', telepon: '', email: '', is_active: true, created_at: '', updated_at: '' },
            ],
        });

        mockedAxios.delete.mockResolvedValueOnce({});

        await useMitraStore.getState().remove(1);

        expect(mockedAxios.delete).toHaveBeenCalledWith('/api/mitra/1');

        const state = useMitraStore.getState();
        expect(state.items).toHaveLength(1);
        expect(state.items[0].id).toBe(2);
    });

    it('should set selected item', () => {
        const item: Mitra = {
            id: 1,
            nama_mitra: 'PT Dipilih',
            jenis_mitra: 'industri',
            alamat: '',
            kontak: '',
            telepon: '',
            email: '',
            is_active: true,
            created_at: '',
            updated_at: '',
        };

        useMitraStore.getState().setSelected(item);

        expect(useMitraStore.getState().selectedItem).toEqual(item);
    });

    it('should handle create errors and throw', async () => {
        mockedAxios.post.mockRejectedValueOnce(
            new Error('Failed to create mitra'),
        );

        await expect(
            useMitraStore.getState().create({ nama_mitra: 'Gagal' }),
        ).rejects.toThrow();

        const state = useMitraStore.getState();
        expect(state.error).toBe('Failed to create mitra');
    });
});