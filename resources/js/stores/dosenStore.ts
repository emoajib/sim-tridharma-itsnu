import { createMasterDataStore } from './masterDataStore';
import type { MDosen } from '@/types/models';

export const useDosenStore = createMasterDataStore<MDosen>('/api/dosen');
