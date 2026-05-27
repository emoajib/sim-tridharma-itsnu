import { createMasterDataStore } from './masterDataStore';
import type { MKurikulum } from '@/types/models';

export const useKurikulumStore = createMasterDataStore<MKurikulum>('/api/kurikulum');
