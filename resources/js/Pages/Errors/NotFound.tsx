import { Head } from '@inertiajs/react';
import { useEffect } from 'react';

export default function NotFound() {
  useEffect(() => {
    document.title = '404 - Halaman Tidak Ditemukan';
  }, []);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <Head title="404 - Halaman Tidak Ditemukan" />
      <div className="text-center px-6">
        <h1 className="text-9xl font-bold text-gray-200">404</h1>
        <h2 className="mt-4 text-2xl font-semibold text-gray-700">Halaman Tidak Ditemukan</h2>
        <p className="mt-2 text-gray-500">Halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
        <a
          href="/"
          className="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          Kembali ke Beranda
        </a>
      </div>
    </div>
  );
}
