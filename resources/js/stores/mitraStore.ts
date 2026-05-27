import { createMasterDataStore } from './masterDataStore';
import type { MMitra } from '@/types/models';

export const useMitraStore = createMasterDataStore<MMitra>('/api/mitra');
