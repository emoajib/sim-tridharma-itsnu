// k6 Load Test — SIM Tridharma ITSNU
// Run: k6 run tests/Load/stress.js

import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';

const errorRate = new Rate('errors');
const loginDuration = new Trend('login_duration');
const pageLoadDuration = new Trend('page_load_duration');

export const options = {
  stages: [
    { duration: '1m', target: 10 },
    { duration: '2m', target: 50 },
    { duration: '2m', target: 100 },
    { duration: '1m', target: 0 },
  ],
  thresholds: {
    errors: ['rate<0.05'],
    login_duration: ['p(95)<2000'],
    page_load_duration: ['p(95)<3000'],
    http_req_duration: ['p(95)<5000'],
  },
};

export default function () {
  const loginPayload = JSON.stringify({
    email: 'admin@example.com',
    password: 'password',
    _token: '',
  });

  const loginRes = http.post(`${BASE_URL}/login`, loginPayload, {
    headers: { 'Content-Type': 'application/json' },
  });

  loginDuration.add(loginRes.timings.duration);
  errorRate.add(loginRes.status !== 200);
  check(loginRes, {
    'login successful': (r) => r.status === 200,
  });

  if (loginRes.status !== 200) {
    sleep(1);
    return;
  }

  const cookies = loginRes.cookies;

  const pages = [
    '/dashboard',
    '/spmi/dashboard',
    '/master-data/fakultas',
    '/master-data/prodi',
    '/master-data/dosen',
    '/portofolio',
    '/spmi/capa',
    '/spmi/audit',
    '/spmi/standar-mutu',
    '/spmi/cycle',
    '/spmi/edps',
    '/spmi/rtm',
    '/spmi/risk',
    '/rkat',
    '/iku',
    '/peringatan',
    '/verifikasi',
    '/prediksi',
    '/rekomendasi',
    '/generator',
    '/integrasi',
  ];

  for (const page of pages) {
    const res = http.get(`${BASE_URL}${page}`, {
      headers: { 'Cookie': Object.entries(cookies).map(([k, v]) => `${k}=${v[0].value}`).join('; ') },
    });

    pageLoadDuration.add(res.timings.duration);
    errorRate.add(res.status !== 200);
    check(res, {
      [`${page} status 200`]: (r) => r.status === 200,
    });

    sleep(0.5);
  }

  sleep(2);
}
