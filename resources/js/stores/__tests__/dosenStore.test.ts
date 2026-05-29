import { describe, it, expect, beforeEach, vi } from 'vitest';
import axios from 'axios';
import { useDosenStore } from '../dosenStore';

vi.mock('axios');
const mockedAxios = vi.mocked(axios);

interface Dosen {
    id: number;
    nama_depan: string;
    nama_belakang?: string;
    nidn: string;
    status_aktivitas: string;
    is_active: boolean;
}

const createDosen = (overrides: Partial<Dosen> = {}): Dosen => ({
    id: 1,
    nama_depan: 'Dr. Test',
    nidn: '000000',
    status_aktivitas: 'aktif',
    is_active: true,
    ...overrides,
});

describe('useDosenStore', () => {
    beforeEach(() => {
        useDosenStore.getState().reset();
        vi.clearAllMocks();
    });

    it('should create store with initial state', () => {
        const state = useDosenStore.getState();
        expect(state.items).toEqual([]);
        expect(state.loading).toBe(false);
        expect(state.error).toBeNull();
    });

    it('should fetch dosen from API', async () => {
        const mockDosen: Dosen[] = [
            createDosen({ id: 1, nama_depan: 'Dr. Santoso', nidn: '123456' }),
            createDosen({ id: 2, nama_depan: 'Dr. Wijaya', nidn: '789012' }),
        ];

        mockedAxios.get.mockResolvedValueOnce({
            data: {
                data: mockDosen,
                current_page: 1,
                last_page: 1,
                total: 2,
                per_page: 10,
                from: 1,
                to: 2,
                links: [],
            },
        });

        await useDosenStore.getState().fetch();

        const state = useDosenStore.getState();
        expect(state.items).toEqual(mockDosen);
        expect(state.loading).toBe(false);
        expect(state.error).toBeNull();
    });

    it('should create a new dosen', async () => {
        const newDosen = createDosen({ id: 3, nama_depan: 'Dr. Baru', nidn: '345678' });

        mockedAxios.post.mockResolvedValueOnce({
            data: { data: newDosen },
        });

        const result = await useDosenStore
            .getState()
            .create({ nama_depan: 'Dr. Baru', nidn: '345678' });

        expect(result).toEqual(newDosen);
        expect(mockedAxios.post).toHaveBeenCalledWith('/api/dosen', {
            nama_depan: 'Dr. Baru',
            nidn: '345678',
        });

        const state = useDosenStore.getState();
        expect(state.items).toContainEqual(newDosen);
    });

    it('should update an existing dosen', async () => {
        useDosenStore.setState({
            items: [createDosen({ id: 1, nama_depan: 'Dr. Lama', nidn: '111111' })],
        });

        const updatedDosen = createDosen({ id: 1, nama_depan: 'Dr. Update', nidn: '222222' });

        mockedAxios.put.mockResolvedValueOnce({
            data: { data: updatedDosen },
        });

        const result = await useDosenStore
            .getState()
            .update(1, { nama_depan: 'Dr. Update', nidn: '222222' });

        expect(result).toEqual(updatedDosen);
        expect(mockedAxios.put).toHaveBeenCalledWith('/api/dosen/1', {
            nama_depan: 'Dr. Update',
            nidn: '222222',
            status_aktivitas: 'aktif',
            is_active: true,
        });

        const state = useDosenStore.getState();
        expect(state.items[0]).toEqual(updatedDosen);
    });

    it('should delete a dosen', async () => {
        useDosenStore.setState({
            items: [
                createDosen({ id: 1, nama_depan: 'Dr. Hapus', nidn: '333333' }),
                createDosen({ id: 2, nama_depan: 'Dr. Lain', nidn: '444444' }),
            ],
        });

        mockedAxios.delete.mockResolvedValueOnce({});

        await useDosenStore.getState().remove(1);

        expect(mockedAxios.delete).toHaveBeenCalledWith('/api/dosen/1');

        const state = useDosenStore.getState();
        expect(state.items).toEqual([
            createDosen({ id: 2, nama_depan: 'Dr. Lain', nidn: '444444' }),
        ]);
    });

    it('should reset to initial state', () => {
        useDosenStore.setState({
            items: [createDosen({ id: 1, nama_depan: 'Test', nidn: '000000' })],
            loading: true,
            error: 'Some error',
        });

        useDosenStore.getState().reset();

        const state = useDosenStore.getState();
        expect(state.items).toEqual([]);
        expect(state.loading).toBe(false);
        expect(state.error).toBeNull();
    });

    it('should handle fetch errors', async () => {
        mockedAxios.get.mockRejectedValueOnce(new Error('Network error'));

        await useDosenStore.getState().fetch();

        const state = useDosenStore.getState();
        expect(state.loading).toBe(false);
        expect(state.error).toBe('Network error');
        expect(state.items).toEqual([]);
    });
});