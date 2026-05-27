import { createMasterDataStore } from './masterDataStore';
import type { MMataKuliah } from '@/types/models';

export const useMataKuliahStore = createMasterDataStore<MMataKuliah>('/api/mata-kuliah');
