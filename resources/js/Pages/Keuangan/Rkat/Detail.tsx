import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';

interface Proposal {
    id: number;
    judul_kegiatan: string;
    deskripsi_kegiatan: string;
    status: string;
    estimasi_biaya: number;
    prodi?: { nama_prodi: string };
    periode?: { tahun_akademik: string; semester: string };
    iku?: { nama_indikator: string; kode_iku: string };
    pengusul?: { name: string };
    logs: {
        id: number;
        action: string;
        keterangan: string | null;
        user: { name: string };
        created_at: string;
    }[];
}

interface Props {
    proposal: Proposal;
}

export default function Detail({ proposal }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        action: '',
        keterangan: '',
    });

    const submitApproval: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('rkat.approve', proposal.id));
    };

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Detail Usulan RKAT</h2>}
        >
            <Head title={`RKAT: ${proposal.judul_kegiatan}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <div className="flex justify-between items-start mb-6">
                            <div>
                                <h3 className="text-2xl font-bold text-gray-900">{proposal.judul_kegiatan}</h3>
                                <p className="text-sm text-gray-500 mt-1">Diajukan oleh: {proposal.pengusul?.name} pada {new Date(proposal.logs[0]?.created_at).toLocaleDateString('id-ID')}</p>
                            </div>
                            <span className={`px-4 py-1 rounded-full text-sm font-bold uppercase ${proposal.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'}`}>
                                {proposal.status}
                            </span>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 border-t pt-6">
                            <div className="space-y-4">
                                <div>
                                    <h4 className="text-xs font-semibold text-gray-400 uppercase tracking-wider">Unit / Program Studi</h4>
                                    <p className="mt-1 text-gray-900 font-medium">{proposal.prodi?.nama_prodi || 'Rektorat'}</p>
                                </div>
                                <div>
                                    <h4 className="text-xs font-semibold text-gray-400 uppercase tracking-wider">Periode Anggaran</h4>
                                    <p className="mt-1 text-gray-900 font-medium">{proposal.periode?.tahun_akademik} ({proposal.periode?.semester})</p>
                                </div>
                                <div>
                                    <h4 className="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kaitan IKU</h4>
                                    <p className="mt-1 text-gray-900 font-medium">[{proposal.iku?.kode_iku}] {proposal.iku?.nama_indikator}</p>
                                </div>
                                <div>
                                    <h4 className="text-xs font-semibold text-gray-400 uppercase tracking-wider">Anggaran Diajukan</h4>
                                    <p className="mt-1 text-2xl font-bold text-indigo-600">{formatCurrency(proposal.estimasi_biaya)}</p>
                                </div>
                            </div>

                            <div className="space-y-4">
                                <div>
                                    <h4 className="text-xs font-semibold text-gray-400 uppercase tracking-wider">Deskripsi & Justifikasi</h4>
                                    <div className="mt-2 p-4 bg-gray-50 rounded-lg text-gray-700 whitespace-pre-wrap text-sm border">
                                        {proposal.deskripsi_kegiatan}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {(proposal.status === 'submitted' || proposal.status === 'draft') && (
                        <div className="bg-white shadow sm:rounded-lg p-6 border-l-4 border-yellow-400">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">Panel Persetujuan</h3>
                            <form onSubmit={submitApproval} className="space-y-4">
                                <div>
                                    <InputLabel htmlFor="keterangan" value="Catatan Persetujuan / Penolakan" />
                                    <textarea
                                        id="keterangan"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        rows={3}
                                        value={data.keterangan}
                                        onChange={(e) => setData('keterangan', e.target.value)}
                                        placeholder="Berikan alasan atau instruksi tambahan..."
                                    />
                                    <InputError message={errors.keterangan} className="mt-2" />
                                </div>
                                <div className="flex gap-4">
                                    <PrimaryButton 
                                        type="submit" 
                                        onClick={() => setData('action', 'approve')}
                                        className="bg-green-600 hover:bg-green-700"
                                        disabled={processing}
                                    >
                                        Setujui Usulan
                                    </PrimaryButton>
                                    <button 
                                        type="submit" 
                                        onClick={() => setData('action', 'reject')}
                                        className="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                                        disabled={processing}
                                    >
                                        Tolak Usulan
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}

                    <div className="bg-white shadow sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">Riwayat Approval</h3>
                        <div className="flow-root">
                            <ul className="-mb-8">
                                {proposal.logs.map((log, idx) => (
                                    <li key={log.id}>
                                        <div className="relative pb-8">
                                            {idx !== proposal.logs.length - 1 && (
                                                <span className="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true" />
                                            )}
                                            <div className="relative flex space-x-3">
                                                <div>
                                                    <span className={`h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white ${
                                                        log.action === 'Submit' ? 'bg-blue-500' : 
                                                        log.action === 'Approve' ? 'bg-green-500' : 
                                                        log.action === 'Reject' ? 'bg-red-500' : 'bg-gray-400'
                                                    }`}>
                                                        <span className="text-white text-xs font-bold">{log.action[0]}</span>
                                                    </span>
                                                </div>
                                                <div className="flex-1 min-w-0 flex justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p className="text-sm text-gray-500">
                                                            <span className="font-medium text-gray-900">{log.user.name}</span>{' '}
                                                            melakukan <span className="font-bold">{log.action}</span>
                                                        </p>
                                                        {log.keterangan && (
                                                            <p className="mt-1 text-sm text-gray-600 italic italic">"{log.keterangan}"</p>
                                                        )}
                                                    </div>
                                                    <div className="text-right text-sm whitespace-nowrap text-gray-500">
                                                        <time>{new Date(log.created_at).toLocaleString('id-ID')}</time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
