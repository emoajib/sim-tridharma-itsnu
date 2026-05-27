import { describe, it, expect, beforeEach, vi } from 'vitest';
import axios from 'axios';
import { createMasterDataStore } from '../masterDataStore';

vi.mock('axios');
const mockedAxios = vi.mocked(axios);

interface TestEntity {
  id: number;
  name: string;
}

describe('createMasterDataStore', () => {
  const endpoint = '/api/test-entities';
  const useStore = createMasterDataStore<TestEntity>(endpoint);

  beforeEach(() => {
    // Reset the store before each test
    useStore.getState().reset();
    vi.clearAllMocks();
  });

  it('should create store with initial state', () => {
    const state = useStore.getState();
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

  it('fetch should set loading state', async () => {
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

    // Start fetch but don't await it yet to check loading state
    const fetchPromise = useStore.getState().fetch();

    // loading should be true while request is in flight
    expect(useStore.getState().loading).toBe(true);

    await fetchPromise;

    // loading should be false after completion
    expect(useStore.getState().loading).toBe(false);
  });

  it('fetch should handle errors gracefully', async () => {
    const errorMessage = 'Network Error';
    mockedAxios.get.mockRejectedValueOnce(new Error(errorMessage));

    await useStore.getState().fetch();

    const state = useStore.getState();
    expect(state.loading).toBe(false);
    expect(state.error).toBe(errorMessage);
    expect(state.items).toEqual([]);
  });

  it('fetch should handle API error response', async () => {
    const apiErrorMessage = 'Internal server error';
    mockedAxios.get.mockRejectedValueOnce({
      response: {
        data: {
          message: apiErrorMessage,
        },
      },
    });

    await useStore.getState().fetch();

    const state = useStore.getState();
    expect(state.loading).toBe(false);
    expect(state.error).toBe(apiErrorMessage);
  });

  it('reset should return to initial state', () => {
    const store = useStore.getState();

    // Mutate the state first
    store.items = [{ id: 1, name: 'Test' }];
    store.loading = true;
    store.error = 'Some error';
    store.selectedItem = { id: 1, name: 'Test' };

    // Reset
    store.reset();

    const state = useStore.getState();
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

  it('fetch should populate items on success', async () => {
    const mockData: TestEntity[] = [
      { id: 1, name: 'Entity 1' },
      { id: 2, name: 'Entity 2' },
    ];

    const mockResponse = {
      data: {
        data: mockData,
        current_page: 1,
        last_page: 2,
        total: 20,
        per_page: 10,
        from: 1,
        to: 10,
        links: [],
      },
    };

    mockedAxios.get.mockResolvedValueOnce(mockResponse);

    await useStore.getState().fetch({ page: 1, per_page: 10 });

    const state = useStore.getState();
    expect(state.items).toEqual(mockData);
    expect(state.pagination.currentPage).toBe(1);
    expect(state.pagination.lastPage).toBe(2);
    expect(state.pagination.total).toBe(20);
    expect(state.pagination.perPage).toBe(10);
  });

  it('fetch should pass query params correctly', async () => {
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

    await useStore.getState().fetch({
      page: 2,
      per_page: 25,
      sort_by: 'name',
      sort_order: 'desc',
      search: 'test',
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(endpoint, {
      params: {
        page: 2,
        per_page: 25,
        sort_by: 'name',
        sort_order: 'desc',
        search: 'test',
      },
    });
  });
});
