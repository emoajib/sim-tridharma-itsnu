import { describe, it, expect, beforeEach, vi } from 'vitest';
import axios from 'axios';
import { useImportHistoryStore } from '../importHistoryStore';

vi.mock('axios');
const mockedAxios = vi.mocked(axios);

describe('useImportHistoryStore', () => {
  beforeEach(() => {
    useImportHistoryStore.getState().reset();
    vi.clearAllMocks();
  });

  it('should fetch paginated history', async () => {
    const mockItems = [
      {
        id: 1,
        type: 'dosen',
        file_name: 'dosen_2024.xlsx',
        total_rows: 100,
        success_rows: 95,
        failed_rows: 5,
        status: 'completed' as const,
        created_at: '2024-01-15T10:00:00Z',
      },
      {
        id: 2,
        type: 'mahasiswa',
        file_name: 'mahasiswa_2024.xlsx',
        total_rows: 200,
        success_rows: 200,
        failed_rows: 0,
        status: 'completed' as const,
        created_at: '2024-01-16T10:00:00Z',
      },
    ];

    const mockResponse = {
      data: {
        data: mockItems,
        current_page: 1,
        last_page: 3,
        total: 25,
        per_page: 10,
        from: 1,
        to: 10,
        links: [],
      },
    };

    mockedAxios.get.mockResolvedValueOnce(mockResponse);

    await useImportHistoryStore.getState().fetch({ page: 1, per_page: 10 });

    const state = useImportHistoryStore.getState();
    expect(state.items).toEqual(mockItems);
    expect(state.loading).toBe(false);
    expect(state.error).toBeNull();
    expect(state.pagination).toEqual({
      currentPage: 1,
      lastPage: 3,
      total: 25,
      perPage: 10,
    });
  });

  it('should handle empty response', async () => {
    const mockResponse = {
      data: {
        data: [],
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 10,
        from: 0,
        to: 0,
        links: [],
      },
    };

    mockedAxios.get.mockResolvedValueOnce(mockResponse);

    await useImportHistoryStore.getState().fetch();

    const state = useImportHistoryStore.getState();
    expect(state.items).toEqual([]);
    expect(state.loading).toBe(false);
    expect(state.error).toBeNull();
    expect(state.pagination.total).toBe(0);
  });

  it('should handle fetch error', async () => {
    const errorMessage = 'Failed to fetch import history';
    mockedAxios.get.mockRejectedValueOnce(new Error('Network Error'));

    await useImportHistoryStore.getState().fetch();

    const state = useImportHistoryStore.getState();
    expect(state.loading).toBe(false);
    expect(state.error).toBe('Network Error');
    expect(state.items).toEqual([]);
  });

  it('should handle paginated fetch with custom params', async () => {
    const mockResponse = {
      data: {
        data: [],
        current_page: 2,
        last_page: 5,
        total: 50,
        per_page: 10,
        from: 11,
        to: 20,
        links: [],
      },
    };

    mockedAxios.get.mockResolvedValueOnce(mockResponse);

    await useImportHistoryStore.getState().fetch({ page: 2, per_page: 10 });

    expect(mockedAxios.get).toHaveBeenCalledWith('/api/import-histories', {
      params: {
        page: 2,
        per_page: 10,
      },
    });

    const state = useImportHistoryStore.getState();
    expect(state.pagination.currentPage).toBe(2);
    expect(state.pagination.total).toBe(50);
  });
});
