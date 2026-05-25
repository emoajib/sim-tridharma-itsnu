import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';
import TextInput from '@/Components/TextInput';
import InputLabel from '@/Components/InputLabel';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import axios from 'axios';

interface Props {
    periode_list: { id: number; kode_periode: string; nama_periode: string }[];
    iku_list: { id: number; nama_indikator: string; kode_iku: string }[];
    prodi_list: { id: number; nama_prodi: string }[];
}

export default function Create({ periode_list, iku_list, prodi_list }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        judul_kegiatan: '',
        deskripsi_kegiatan: '',
        periode_id: '',
        prodi_id: '',
        iku_id: '',
        estimasi_biaya: '0',
    });

    const [paguInfo, setPaguInfo] = useState<{ available: boolean; remaining: number } | null>(null);

    useEffect(() => {
        if (data.prodi_id && data.periode_id && parseFloat(data.estimasi_biaya) > 0) {
            const checkPagu = async () => {
                try {
                    const response = await axios.post(route('rkat.check-pagu'), {
                        prodi_id: data.prodi_id,
                        periode_id: data.periode_id,
                        amount: data.estimasi_biaya,
                    });
                    setPaguInfo(response.data.data);
                } catch (e) {
                    console.error('Failed to check pagu', e);
                }
            };
            checkPagu();
        } else {
            setPaguInfo(null);
        }
    }, [data.prodi_id, data.periode_id, data.estimasi_biaya]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('rkat.store'));
    };

    const formatCurrency = (value: number) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Buat Usulan RKAT</h2>}
        >
            <Head title="Buat Usulan RKAT" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <form onSubmit={submit}>
                            <div className="space-y-6">
                                <div>
                                    <InputLabel htmlFor="judul_kegiatan" value="Judul Usulan Kegiatan" />
                                    <TextInput
                                        id="judul_kegiatan"
                                        className="mt-1 block w-full"
                                        value={data.judul_kegiatan}
                                        onChange={(e) => setData('judul_kegiatan', e.target.value)}
                                        required
                                        placeholder="Contoh: Pengadaan Fasilitas Laboratorium Komputer"
                                    />
                                    <InputError message={errors.judul_kegiatan} className="mt-2" />
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel htmlFor="periode_id" value="Periode Akademik" />
                                        <select
                                            id="periode_id"
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            value={data.periode_id}
                                            onChange={(e) => setData('periode_id', e.target.value)}
                                            required
                                        >
                                            <option value="">Pilih Periode</option>
                                            {periode_list.map(p => (
                                                <option key={p.id} value={p.id}>{p.nama_periode}</option>
                                            ))}
                                        </select>
                                        <InputError message={errors.periode_id} className="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel htmlFor="prodi_id" value="Program Studi / Unit" />
                                        <select
                                            id="prodi_id"
                                            className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                            value={data.prodi_id}
                                            onChange={(e) => setData('prodi_id', e.target.value)}
                                            required
                                        >
                                            <option value="">Pilih Prodi</option>
                                            {prodi_list.map(p => (
                                                <option key={p.id} value={p.id}>{p.nama_prodi}</option>
                                            ))}
                                        </select>
                                        <InputError message={errors.prodi_id} className="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <InputLabel htmlFor="iku_id" value="Kaitan dengan IKU" />
                                    <select
                                        id="iku_id"
                                        className="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                        value={data.iku_id}
                                        onChange={(e) => setData('iku_id', e.target.value)}
                                        required
                                    >
                                        <option value="">Pilih IKU</option>
                                        {iku_list.map(i => (
                                            <option key={i.id} value={i.id}>[{i.kode_iku}] {i.nama_indikator}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors.iku_id} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="estimasi_biaya" value="Anggaran yang Diajukan (Rp)" />
                                    <TextInput
                                        id="estimasi_biaya"
                                        type="number"
                                        className="mt-1 block w-full font-mono text-lg"
                                        value={data.estimasi_biaya}
                                        onChange={(e) => setData('estimasi_biaya', e.target.value)}
                                        required
                                    />
                                    {paguInfo && (
                                        <div className={`mt-2 text-sm font-medium ${paguInfo.available ? 'text-green-600' : 'text-red-600'}`}>
                                            {paguInfo.available 
                                                ? `Sisa Pagu Tersedia: ${formatCurrency(paguInfo.remaining)}`
                                                : `Melebihi Sisa Pagu! Sisa Tersedia: ${formatCurrency(paguInfo.remaining)}`
                                            }
                                        </div>
                                    )}
                                    <InputError message={errors.estimasi_biaya} className="mt-2" />
                                </div>

                                <div>
                                    <InputLabel htmlFor="deskripsi_kegiatan" value="Deskripsi / Justifikasi" />
                                    <textarea
                                        id="deskripsi_kegiatan"
                                        className="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows={5}
                                        value={data.deskripsi_kegiatan}
                                        onChange={(e) => setData('deskripsi_kegiatan', e.target.value)}
                                        required
                                        placeholder="Jelaskan secara detail mengenai tujuan dan rincian penggunaan anggaran..."
                                    />
                                    <InputError message={errors.deskripsi_kegiatan} className="mt-2" />
                                </div>

                                <div className="flex items-center justify-end mt-6">
                                    <Link href={route('rkat.index')} className="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                        Batal
                                    </Link>
                                    <PrimaryButton disabled={processing || (paguInfo !== null && !paguInfo.available)}>
                                        Ajukan Usulan
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
