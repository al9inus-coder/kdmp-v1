// Versi cache dinaikkan agar isi cache lama — yang sempat menyimpan halaman
// ber-login — ikut dibuang saat pemasangan berikutnya.
const CACHE_NAME = 'kdmp-pwa-v2';

// Hanya berkas statis. Halaman HTML TIDAK PERNAH disimpan: isinya bergantung
// pada siapa yang login, sehingga menyimpannya berisiko menampilkan halaman
// milik pengguna lain di perangkat yang sama.
const ASSETS_TO_CACHE = [
  '/manifest.json',
  '/favicon.ico',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => cache.addAll(ASSETS_TO_CACHE))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((names) => Promise.all(
        names.filter((n) => n !== CACHE_NAME).map((n) => caches.delete(n))
      ))
      .then(() => self.clients.claim())
  );
});

/** Aset statis yang aman disimpan: milik sendiri dan tidak bergantung sesi. */
function bolehDisimpan(request, response) {
  if (request.method !== 'GET' || !response || !response.ok || response.type !== 'basic') {
    return false;
  }

  const url = new URL(request.url);

  if (url.origin !== self.location.origin) return false;

  // Jangan pernah menyimpan dokumen HTML maupun balasan API.
  if (request.mode === 'navigate' || request.destination === 'document') return false;
  if ((response.headers.get('content-type') || '').includes('text/html')) return false;

  return /^\/(build|images|css|js)\//.test(url.pathname)
      || /\.(css|js|png|jpe?g|svg|webp|ico|woff2?)$/.test(url.pathname)
      || ASSETS_TO_CACHE.includes(url.pathname);
}

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;

  event.respondWith(
    fetch(request)
      .then((response) => {
        if (bolehDisimpan(request, response)) {
          const salinan = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, salinan));
        }
        return response;
      })
      .catch(async () => {
        const tersimpan = await caches.match(request);
        if (tersimpan) return tersimpan;

        // Halaman tidak pernah disimpan, jadi saat luring beri pesan jujur
        // alih-alih menampilkan isi basi.
        if (request.mode === 'navigate') {
          return new Response(
            '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">'
            + '<meta name="viewport" content="width=device-width, initial-scale=1">'
            + '<title>Tidak ada koneksi</title></head>'
            + '<body style="font-family:system-ui;display:flex;align-items:center;justify-content:center;'
            + 'height:100vh;margin:0;color:#334155;text-align:center;padding:24px">'
            + '<div><h1 style="font-size:18px;margin:0 0 8px">Tidak ada koneksi</h1>'
            + '<p style="font-size:14px;color:#64748b;margin:0">Sambungkan internet lalu muat ulang halaman ini.</p></div>'
            + '</body></html>',
            { headers: { 'Content-Type': 'text/html; charset=utf-8' }, status: 503 }
          );
        }

        return Response.error();
      })
  );
});

// Push Notification Event Listener
self.addEventListener('push', (event) => {
  const data = event.data ? event.data.json() : { title: 'KDMP', body: 'Ada pembaruan status dokumen' };
  const options = {
    body: data.body,
    icon: '/favicon.ico',
    badge: '/favicon.ico',
    vibrate: [100, 50, 100],
    data: { url: data.url || '/asisten' }
  };

  event.waitUntil(self.registration.showNotification(data.title, options));
});

// Notification Click Event Listener
self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  event.waitUntil(clients.openWindow(event.notification.data.url));
});
