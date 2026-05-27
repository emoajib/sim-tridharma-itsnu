import { Head } from '@inertiajs/react';
import { useEffect } from 'react';

export default function ServerError() {
  useEffect(() => {
    document.title = '500 - Kesalahan Server';
  }, []);

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <Head title="500 - Kesalahan Server" />
      <div className="text-center px-6">
        <h1 className="text-9xl font-bold text-gray-200">500</h1>
        <h2 className="mt-4 text-2xl font-semibold text-gray-700">Terjadi Kesalahan</h2>
        <p className="mt-2 text-gray-500">Maaf, terjadi kesalahan pada server. Silakan coba lagi beberapa saat.</p>
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
