import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function TwoFactorForm({ className = '' }: { className?: string }) {
  const { auth } = usePage().props as any;
  const user = auth?.user;
  const twoFactorEnabled = user?.two_factor_enabled ?? false;
  const [loading, setLoading] = useState(false);

  const handleSetup = () => {
    router.visit(route('2fa.setup'));
  };

  const handleDisable = () => {
    if (!confirm('Apakah Anda yakin ingin menonaktifkan Two-Factor Authentication?')) return;
    setLoading(true);
    router.post(route('2fa.disable'), { password: '' }, {
      onFinish: () => setLoading(false),
    });
  };

  return (
    <div className={className}>
      <h3 className="text-lg font-medium text-gray-900">Two-Factor Authentication</h3>
      <p className="mt-1 text-sm text-gray-600">
        Tambahkan keamanan ekstra dengan Two-Factor Authentication.
      </p>
      <div className="mt-4">
        {twoFactorEnabled ? (
          <div className="flex items-center gap-4">
            <span className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
              ✅ Aktif
            </span>
            <button
              onClick={handleDisable}
              disabled={loading}
              className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 text-sm"
            >
              {loading ? 'Menonaktifkan...' : 'Nonaktifkan 2FA'}
            </button>
          </div>
        ) : (
          <button
            onClick={handleSetup}
            className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
          >
            Aktifkan Two-Factor Authentication
          </button>
        )}
      </div>
    </div>
  );
}
