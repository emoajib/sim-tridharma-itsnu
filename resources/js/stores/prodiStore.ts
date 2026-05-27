import { createMasterDataStore } from './masterDataStore';
import type { MProdi } from '@/types/models';

export const useProdiStore = createMasterDataStore<MProdi>('/api/prodi');
