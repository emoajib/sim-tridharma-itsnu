import { createMasterDataStore } from './masterDataStore';
import type { MMahasiswa } from '@/types/models';

export const useMahasiswaStore = createMasterDataStore<MMahasiswa>('/api/mahasiswa');
