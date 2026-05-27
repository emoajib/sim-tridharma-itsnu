import { create } from 'zustand';
import axios from 'axios';
import type { PaginatedData, QueryParams } from '@/types/models';

interface PaginationState {
  currentPage: number;
  lastPage: number;
  total: number;
  perPage: number;
}

interface EntityActions<T> {
  setSelected: (item: T | null) => void;
  fetch: (params?: QueryParams) => Promise<void>;
  create: (data: Partial<T>) => Promise<T>;
  update: (id: number, data: Partial<T>) => Promise<T>;
  remove: (id: number) => Promise<void>;
  reset: () => void;
}

export interface EntityState<T> {
  items: T[];
  loading: boolean;
  error: string | null;
  pagination: PaginationState;
  selectedItem: T | null;
}

export type EntityStore<T> = EntityState<T> & EntityActions<T>;

export function createMasterDataStore<T extends { id: number }>(
  apiEndpoint: string,
) {
  return create<EntityStore<T>>((set, get) => ({
    items: [],
    loading: false,
    error: null,
    pagination: { currentPage: 1, lastPage: 1, total: 0, perPage: 10 },
    selectedItem: null,

    setSelected: (item) => set({ selectedItem: item }),

    fetch: async (params?: QueryParams) => {
      set({ loading: true, error: null });
      try {
        const response = await axios.get<PaginatedData<T>>(apiEndpoint, {
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
        const message = err?.response?.data?.message ?? err?.message ?? 'Failed to fetch data';
        set({ error: message, loading: false });
      }
    },

    create: async (data) => {
      set({ loading: true, error: null });
      try {
        const response = await axios.post<{ data: T }>(apiEndpoint, data);
        const newItem = response.data.data;
        set((state) => ({ items: [...state.items, newItem], loading: false }));
        return newItem;
      } catch (err: any) {
        const message = err?.response?.data?.message ?? err?.message ?? 'Failed to create data';
        set({ error: message, loading: false });
        throw err;
      }
    },

    update: async (id, data) => {
      set({ loading: true, error: null });
      try {
        const response = await axios.put<{ data: T }>(`${apiEndpoint}/${id}`, data);
        const updatedItem = response.data.data;
        set((state) => ({
          items: state.items.map((item) => (item.id === id ? updatedItem : item)),
          selectedItem: state.selectedItem?.id === id ? updatedItem : state.selectedItem,
          loading: false,
        }));
        return updatedItem;
      } catch (err: any) {
        const message = err?.response?.data?.message ?? err?.message ?? 'Failed to update data';
        set({ error: message, loading: false });
        throw err;
      }
    },

    remove: async (id) => {
      set({ loading: true, error: null });
      try {
        await axios.delete(`${apiEndpoint}/${id}`);
        set((state) => ({
          items: state.items.filter((item) => item.id !== id),
          selectedItem: state.selectedItem?.id === id ? null : state.selectedItem,
          loading: false,
        }));
      } catch (err: any) {
        const message = err?.response?.data?.message ?? err?.message ?? 'Failed to delete data';
        set({ error: message, loading: false });
        throw err;
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
}
