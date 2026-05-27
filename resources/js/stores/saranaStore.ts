import { createMasterDataStore } from './masterDataStore';
import type { MSarana } from '@/types/models';

export const useSaranaStore = createMasterDataStore<MSarana>('/api/sarana');
