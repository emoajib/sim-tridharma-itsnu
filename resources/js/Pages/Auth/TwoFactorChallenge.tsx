import { Head, useForm, router } from '@inertiajs/react';

export default function TwoFactorChallenge() {
  const { data, setData, post, processing, errors } = useForm({
    code: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(route('2fa.verify'), {
      preserveScroll: true,
      onSuccess: () => {
        router.visit(route('dashboard'));
      },
    });
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50">
      <Head title="2FA - Verifikasi" />
      <div className="max-w-md w-full p-8 bg-white rounded-lg shadow">
        <h1 className="text-2xl font-bold mb-2">Verifikasi Two-Factor</h1>
        <p className="text-gray-500 mb-6">
          Masukkan kode dari aplikasi authenticator Anda atau gunakan kode recovery.
        </p>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">
              Kode Verifikasi
            </label>
            <input
              type="text"
              value={data.code}
              onChange={(e) => setData('code', e.target.value)}
              className="mt-1 block w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 text-center text-2xl tracking-widest"
              placeholder="000000"
              required
              autoFocus
            />
            <p className="text-xs text-gray-400 mt-1">
              Masukkan 6 digit kode dari aplikasi authenticator, atau kode recovery 8 karakter (XXXX-XXXX).
            </p>
            {errors.code && (
              <p className="text-red-500 text-sm mt-1">{errors.code}</p>
            )}
          </div>

          <button
            type="submit"
            disabled={processing}
            className="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
          >
            {processing ? 'Memverifikasi...' : 'Verifikasi'}
          </button>
        </form>
      </div>
    </div>
  );
}
