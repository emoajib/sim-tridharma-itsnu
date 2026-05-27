export interface MProdi {
    id: number;
    kode_prodi: string;
    nama_prodi: string;
    fakultas_id: number;
    jenjang: string;
    akreditasi?: string;
    sk_akreditasi?: string;
    tanggal_kadaluarsa?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
    lembaga_akreditasi_id?: number;
}

export interface MFakultas {
    id: number;
    kode_fakultas: string;
    nama_fakultas: string;
    alamat?: string;
    telepon?: string;
    email?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface MDosen {
    id: number;
    nidn: string;
    nip?: string;
    nama_depan: string;
    nama_belakang?: string;
    gelar_depan?: string;
    gelar_belakang?: string;
    tempat_lahir?: string;
    tanggal_lahir?: string;
    jenis_kelamin?: string;
    prodi_id?: number;
    pendidikan_terakhir?: string;
    jabatan_fungsional?: string;
    status_aktivitas: string;
    email?: string;
    telepon?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
    sinta_id?: string;
    sinta_score_overall?: number;
    sinta_score_3yr?: number;
    status_verifikasi_sinta?: string;
}

export interface MMahasiswa {
    id: number;
    nim: string;
    nama: string;
    prodi_id: number;
    angkatan: string;
    status: string;
    email?: string;
    telepon?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface MMataKuliah {
    id: number;
    kode_mk: string;
    nama_mk: string;
    sks: number;
    prodi_id: number;
    semester?: number;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface MKurikulum {
    id: number;
    nama_kurikulum: string;
    prodi_id: number;
    tahun_berlaku: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface MMitra {
    id: number;
    nama_mitra: string;
    jenis_mitra: string;
    alamat?: string;
    kontak?: string;
    telepon?: string;
    email?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface MSarana {
    id: number;
    prodi_id: number;
    nama_sarana: string;
    jenis_sarana: string;
    jumlah: number;
    kondisi: string;
    tanggal_kalibrasi?: string;
    tanggal_kalibrasi_berikut?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface MPeriodeAkademik {
    id: number;
    kode_periode: string;
    nama_periode: string;
    tanggal_mulai?: string;
    tanggal_selesai?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface MLembagaAkreditasi {
    id: number;
    nama_lembaga: string;
    singkatan: string;
    deskripsi?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
}

export interface MInstrumenAkreditasi {
    id: number;
    lembaga_id: number;
    nama_instrumen: string;
    matriks_kriteria?: any;
    created_at?: string;
    updated_at?: string;
}

export interface MIndikatorAkreditasi {
    id: number;
    kode_indikator: string;
    nama_indikator: string;
    kriteria: string;
    bobot: number;
    target?: string;
    jenis_akreditasi: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
    instrumen_id?: number;
}

export interface MStandarMutu {
    id: number;
    kategori: string;
    kode_standar: string;
    nama_standar: string;
    deskripsi?: string;
    sumber?: string;
    referensi_regulasi?: string;
    target_nilai?: number;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface MSetting {
    id: number;
    key: string;
    value?: string;
    type: string;
    description?: string;
    created_at?: string;
    updated_at?: string;
}

export interface MAlumni {
    id: number;
    mahasiswa_id?: number;
    nim: string;
    nama: string;
    prodi_id: number;
    tahun_lulus: string;
    masa_tunggu?: number;
    gaji_pertama?: number;
    pekerjaan?: string;
    is_active: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface Users {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    remember_token?: string;
    created_at?: string;
    updated_at?: string;
    dosen_id?: number;
    prodi_id?: number;
    is_active: boolean;
}

export interface MRoles {
    id: number;
    name: string;
    guard_name: string;
    created_at?: string;
    updated_at?: string;
}

export interface MPermissions {
    id: number;
    name: string;
    guard_name: string;
    created_at?: string;
    updated_at?: string;
}

export interface TrxBkd {
    id: number;
    dosen_id: number;
    prodi_id: number;
    periode_id: number;
    total_sks_mengajar: number;
    total_sks_penelitian: number;
    total_sks_pkm: number;
    total_sks_penunjang: number;
    total_sks: number;
    status: string;
    catatan?: string;
    is_verified: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface TrxKegiatanPendidikan {
    id: number;
    dosen_id: number;
    prodi_id: number;
    periode_id: number;
    nama_kegiatan: string;
    jenis_kegiatan: string;
    mata_kuliah_id?: number;
    sks: number;
    jumlah_mahasiswa?: number;
    jumlah_pertemuan?: number;
    is_verified: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface TrxPenelitian {
    id: number;
    dosen_id: number;
    prodi_id: number;
    periode_id: number;
    judul_penelitian: string;
    jenis_penelitian: string;
    sumber_dana?: string;
    jumlah_dana?: number;
    tahun_pelaksanaan: string;
    is_verified: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface TrxPublikasi {
    id: number;
    dosen_id: number;
    prodi_id: number;
    periode_id?: number;
    judul_publikasi: string;
    jenis_publikasi: string;
    tingkat: string;
    link?: string;
    tahun: string;
    is_verified: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface TrxPkm {
    id: number;
    dosen_id: number;
    prodi_id: number;
    periode_id: number;
    judul_pkm: string;
    jenis_pkm: string;
    lokasi?: string;
    sumber_dana?: string;
    jumlah_dana?: number;
    tahun_pelaksanaan: string;
    is_verified: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface TrxPenunjang {
    id: number;
    dosen_id: number;
    prodi_id: number;
    periode_id: number;
    nama_kegiatan: string;
    jenis_kegiatan: string;
    tingkat?: string;
    peran?: string;
    tahun: string;
    is_verified: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface TrxMahasiswaBimbingan {
    id: number;
    dosen_id: number;
    mahasiswa_id: number;
    prodi_id: number;
    periode_id: number;
    jenis_bimbingan: string;
    judul?: string;
    status: string;
    is_verified: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface TrxAuditMutu {
    id: number;
    prodi_id: number;
    periode_id: number;
    judul_audit: string;
    tanggal_audit: string;
    auditor?: string;
    temuan?: string;
    rekomendasi?: string;
    status: string;
    tindak_lanjut?: string;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
    standar_mutu_id?: number;
    severity: string;
    pic_user_id?: number;
    auditor_user_id?: number;
    deadline_tindak_lanjut?: string;
    closed_at?: string;
    evidence_file?: string;
    verification_note?: string;
    verified_by?: number;
    verified_at?: string;
    is_locked: boolean;
    locked_at?: string;
    spmi_cycle_id?: number;
}

export interface TrxRiskRegister {
    id: number;
    prodi_id: number;
    periode_id: number;
    nama_risiko: string;
    kategori: string;
    dampak: string;
    probabilitas: string;
    skor_risiko: string;
    mitigasi?: string;
    status: string;
    penanggung_jawab?: string;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface TrxPemenuhanIndikator {
    id: number;
    prodi_id: number;
    periode_id: number;
    indikator_id: number;
    capaian?: string;
    nilai?: number;
    status: string;
    catatan?: string;
    is_verified: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface TrxSkorAkreditasi {
    id: number;
    prodi_id: number;
    periode_id: number;
    skor_total: number;
    skor_prediksi?: number;
    confidence_interval?: number;
    probabilitas_unggul?: number;
    probabilitas_baik_sekali?: number;
    probabilitas_baik?: number;
    sumber_data: string;
    is_final: boolean;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface ImportHistories {
    id: number;
    type: string;
    file_name: string;
    file_path?: string;
    total_rows: number;
    success_rows: number;
    failed_rows: number;
    errors?: any;
    user_id?: number;
    status: string;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: { url: string | null; label: string; active: boolean }[];
}

export interface ApiResponse<T = any> {
    success: boolean;
    message?: string;
    data?: T;
    errors?: Record<string, string[]>;
}

export interface QueryParams {
    search?: string;
    page?: number;
    per_page?: number;
    sort_by?: string;
    sort_order?: 'asc' | 'desc';
    filters?: Record<string, any>;
}

export interface SelectOption {
    value: string | number;
    label: string;
}

export interface ImportHistoryRow {
    id: number;
    type: string;
    file_name: string;
    total_rows: number;
    success_rows: number;
    failed_rows: number;
    errors?: any;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    user?: { id: number; name: string };
    created_at: string;
}
