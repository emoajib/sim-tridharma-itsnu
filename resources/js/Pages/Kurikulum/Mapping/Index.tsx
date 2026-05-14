import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

interface Cpl {
    id: number;
    kode_cpl: string;
    deskripsi: string;
    jenis: string;
}

interface MataKuliah {
    id: number;
    kode_mk: string;
    nama_mk: string;
    sks: number;
    semester: number;
}

interface MappingItem {
    cpl_id: number;
    mata_kuliah_id: number;
    tingkat: string;
}

interface Prodi {
    id: number;
    nama_prodi: string;
}

interface Kurikulum {
    id: number;
    nama_kurikulum: string;
}

interface Props {
    cpls: Cpl[];
    mata_kuliahs: MataKuliah[];
    mapping: MappingItem[];
    prodi_list: Prodi[];
    kurikulum_list: Kurikulum[];
    selectedProdi: number | null;
    selectedKurikulum: number | null;
    success?: string;
}

export default function MappingIndex({
    cpls,
    mata_kuliahs,
    mapping,
    prodi_list,
    kurikulum_list,
    selectedProdi,
    selectedKurikulum,
    success,
}: Props) {
    const mappedSet = new Set(mapping.map((m) => `${m.cpl_id}-${m.mata_kuliah_id}`));

    function isMapped(cplId: number, mkId: number) {
        return mappedSet.has(`${cplId}-${mkId}`);
    }

    function toggleMapping(cplId: number, mkId: number) {
        router.post(route('kurikulum.mapping.toggle'), {
            cpl_id: cplId,
            mata_kuliah_id: mkId,
            prodi_id: selectedProdi,
            kurikulum_id: selectedKurikulum,
        });
    }

    function changeProdi(e: React.ChangeEvent<HTMLSelectElement>) {
        router.get(route('kurikulum.mapping'), {
            prodi_id: e.target.value || undefined,
            kurikulum_id: selectedKurikulum || undefined,
        }, { preserveState: true, replace: true });
    }

    function changeKurikulum(e: React.ChangeEvent<HTMLSelectElement>) {
        router.get(route('kurikulum.mapping'), {
            prodi_id: selectedProdi || undefined,
            kurikulum_id: e.target.value || undefined,
        }, { preserveState: true, replace: true });
    }

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Mapping CPL-MK</h2>}
        >
            <Head title="Mapping CPL-MK" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {/* Breadcrumb */}
                    <nav className="mb-4 text-sm text-gray-500">
                        <Link href={route('dashboard')} className="hover:text-indigo-600">Dashboard</Link>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Kurikulum</span>
                        <span className="mx-2">/</span>
                        <span className="text-gray-700">Mapping CPL-MK</span>
                    </nav>

                    {success && (
                        <div className="mb-4 rounded-lg bg-green-100 p-4 text-sm text-green-700">{success}</div>
                    )}

                    {/* Filters */}
                    <div className="mb-6 rounded-lg bg-white p-4 shadow-sm">
                        <div className="flex flex-wrap gap-4">
                            <div>
                                <label className="mb-1 block text-xs font-medium text-gray-600">Program Studi</label>
                                <select
                                    value={selectedProdi ?? ''}
                                    onChange={changeProdi}
                                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Pilih Prodi</option>
                                    {prodi_list.map((p) => (
                                        <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="mb-1 block text-xs font-medium text-gray-600">Kurikulum</label>
                                <select
                                    value={selectedKurikulum ?? ''}
                                    onChange={changeKurikulum}
                                    className="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Pilih Kurikulum</option>
                                    {kurikulum_list.map((k) => (
                                        <option key={k.id} value={k.id}>{k.nama_kurikulum}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Matrix */}
                    <div className="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="sticky left-0 z-10 whitespace-nowrap bg-gray-50 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            CPL
                                        </th>
                                        {mata_kuliahs.map((mk) => (
                                            <th
                                                key={mk.id}
                                                className="whitespace-nowrap px-3 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500"
                                                title={`${mk.kode_mk} - ${mk.nama_mk}`}
                                            >
                                                <div>{mk.kode_mk}</div>
                                                <div className="text-[10px] font-normal text-gray-400">S{mk.semester}</div>
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 bg-white">
                                    {cpls.length === 0 ? (
                                        <tr>
                                            <td colSpan={mata_kuliahs.length + 1} className="px-6 py-12 text-center text-gray-500">
                                                {!selectedProdi || !selectedKurikulum
                                                    ? 'Pilih Prodi dan Kurikulum terlebih dahulu'
                                                    : 'Tidak ada data CPL'}
                                            </td>
                                        </tr>
                                    ) : (
                                        cpls.map((cpl) => (
                                            <tr key={cpl.id} className="hover:bg-gray-50">
                                                <td className="sticky left-0 z-10 whitespace-nowrap bg-white px-4 py-3 text-sm font-medium text-gray-900 hover:bg-gray-50">
                                                    <div>{cpl.kode_cpl}</div>
                                                    <div className="text-xs text-gray-500">{cpl.jenis}</div>
                                                </td>
                                                {mata_kuliahs.map((mk) => {
                                                    const mapped = isMapped(cpl.id, mk.id);
                                                    return (
                                                        <td key={mk.id} className="px-3 py-2 text-center">
                                                            <button
                                                                onClick={() => toggleMapping(cpl.id, mk.id)}
                                                                className={`mx-auto flex h-8 w-8 items-center justify-center rounded-md border text-sm transition ${
                                                                    mapped
                                                                        ? 'border-green-300 bg-green-100 text-green-700 hover:bg-green-200'
                                                                        : 'border-gray-200 bg-gray-50 text-gray-300 hover:border-gray-300 hover:bg-gray-100'
                                                                }`}
                                                                title={mapped ? 'Klik untuk hapus mapping' : 'Klik untuk tambah mapping'}
                                                            >
                                                                {mapped ? (
                                                                    <svg className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                                                        <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                ) : (
                                                                    <span className="text-xs">+</span>
                                                                )}
                                                            </button>
                                                        </td>
                                                    );
                                                })}
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {/* Legend */}
                    <div className="mt-4 flex items-center gap-6 text-sm text-gray-600">
                        <div className="flex items-center gap-2">
                            <span className="inline-block h-4 w-4 rounded border border-green-300 bg-green-100" />
                            <span>Telah dipetakan</span>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="inline-block h-4 w-4 rounded border border-gray-200 bg-gray-50" />
                            <span>Belum dipetakan</span>
                        </div>
                    </div>

                    {/* Back link */}
                    <div className="mt-6">
                        <Link href={route('dashboard')} className="text-sm text-indigo-600 hover:text-indigo-800">
                            &larr; Kembali ke Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
