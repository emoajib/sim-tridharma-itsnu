import { create } from 'zustand';
import axios from 'axios';
import type { ImportHistoryRow, QueryParams, PaginatedData } from '@/types/models';

interface PaginationState {
  currentPage: number;
  lastPage: number;
  total: number;
  perPage: number;
}

interface ImportHistoryState {
  items: ImportHistoryRow[];
  loading: boolean;
  error: string | null;
  pagination: PaginationState;
  selectedItem: ImportHistoryRow | null;
  setSelected: (item: ImportHistoryRow | null) => void;
  fetch: (params?: QueryParams) => Promise<void>;
  reset: () => void;
}

export const useImportHistoryStore = create<ImportHistoryState>((set) => ({
  items: [],
  loading: false,
  error: null,
  pagination: { currentPage: 1, lastPage: 1, total: 0, perPage: 10 },
  selectedItem: null,

  setSelected: (item) => set({ selectedItem: item }),

  fetch: async (params?: QueryParams) => {
    set({ loading: true, error: null });
    try {
      const response = await axios.get<PaginatedData<ImportHistoryRow>>('/api/import-histories', {
        params: {
          page: params?.page ?? 1,
          per_page: params?.per_page ?? 10,
          sort_by: params?.sort_by,
          sort_order: params?.sort_order,
          search: params?.search,
          ...params?.filters,
        },
      });
      const { data, current_page, last_page, total, per_page } = response.data;
      set({
        items: data,
        pagination: { currentPage: current_page, lastPage: last_page, total, perPage: per_page },
        loading: false,
      });
    } catch (err: any) {
      const message = err?.response?.data?.message ?? err?.message ?? 'Failed to fetch import history';
      set({ error: message, loading: false });
    }
  },

  reset: () =>
    set({
      items: [],
      loading: false,
      error: null,
      pagination: { currentPage: 1, lastPage: 1, total: 0, perPage: 10 },
      selectedItem: null,
    }),
}));
