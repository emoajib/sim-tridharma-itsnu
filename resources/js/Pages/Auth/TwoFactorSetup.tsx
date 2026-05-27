import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';

interface Props {
  secret: string;
  qrCodeUrl: string;
  enabled?: boolean;
  recoveryCodes?: string[];
}

export default function TwoFactorSetup({ secret, qrCodeUrl, enabled = false, recoveryCodes = [] }: Props) {
  const { data, setData, post, processing, errors } = useForm({ code: '' });
  const [showRecovery, setShowRecovery] = useState<string[]>(recoveryCodes);

  const handleConfirm = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('2fa.confirm'), {
      preserveScroll: true,
    });
  };

  if (enabled) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-gray-50">
        <Head title="2FA - Berhasil" />
        <div className="max-w-md w-full p-8 bg-white rounded-lg shadow text-center">
          <h1 className="text-2xl font-bold text-green-600 mb-4">
            Two-Factor Authentication Aktif!
          </h1>
          <p className="text-gray-600 mb-4">
            Akun Anda sekarang lebih aman dengan 2FA.
          </p>
          {recoveryCodes.length > 0 && (
            <>
              <p className="text-sm text-gray-500 mb-2">
                Simpan kode recovery berikut di tempat aman. Setiap kode hanya bisa digunakan sekali.
              </p>
              <div className="mt-4 p-4 bg-gray-100 rounded-lg text-left font-mono text-sm">
                {recoveryCodes.map((code, i) => (
                  <div key={i} className="py-1">• {code}</div>
                ))}
              </div>
            </>
          )}
          <a
            href="/profile"
            className="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
          >
            Kembali ke Profil
          </a>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <Head title="2FA - Setup" />
      <div className="max-w-md w-full p-8 bg-white rounded-lg shadow">
        <h1 className="text-2xl font-bold mb-2">Setup Two-Factor Authentication</h1>
        <p className="text-gray-500 mb-6">
          Scan QR code dengan Google Authenticator atau aplikasi TOTP lainnya.
        </p>

        <div className="flex justify-center mb-6">
          <img src={qrCodeUrl} alt="QR Code" className="w-48 h-48" />
        </div>

        <p className="text-sm text-gray-500 mb-4 text-center font-mono">
          Atau masukkan kode manual:{' '}
          <strong className="select-all">{secret}</strong>
        </p>

        <form onSubmit={handleConfirm} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">
              Kode Verifikasi
            </label>
            <input
              type="text"
              value={data.code}
              onChange={(e) => setData('code', e.target.value)}
              maxLength={6}
              className="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-center text-2xl tracking-widest"
              placeholder="000000"
              required
              autoFocus
            />
            {errors.code && (
              <p className="text-red-500 text-sm mt-1">{errors.code}</p>
            )}
          </div>

          <button
            type="submit"
            disabled={processing}
            className="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {processing ? 'Memverifikasi...' : 'Aktifkan 2FA'}
          </button>
        </form>
      </div>
    </div>
  );
}
