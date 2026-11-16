/*
 * Hampi · service worker
 *
 * - Páginas públicas: red primero; si no hay señal, la última copia guardada.
 * - Archivos estáticos (CSS/JS con huella, fuentes, imágenes): caché primero.
 * - Nunca guarda paneles privados (citas, administración, personal).
 */
const VERSION = 'hampi-v1';
const PAGINAS = `${VERSION}-paginas`;
const ESTATICOS = `${VERSION}-estaticos`;
const OFFLINE = '/offline.html';
const PUBLICAS = ['/', '/medicos-hoy', '/pizarrin-vacunas', '/campanas-preventivas', '/directorio-postas'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(PAGINAS)
            .then((cache) => cache.addAll([OFFLINE, ...PUBLICAS]))
            .catch(() => {})
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((claves) => Promise.all(claves.filter((clave) => !clave.startsWith(VERSION)).map((clave) => caches.delete(clave))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (/^\/(build|fonts|images|icons)\//.test(url.pathname) || url.pathname.startsWith('/fotos/')) {
        event.respondWith(cacheFirst(request));
        return;
    }

    if (request.mode === 'navigate') {
        const esPublica = PUBLICAS.includes(url.pathname)
            && (url.search === '' || url.search === '?origen=app');
        event.respondWith(networkFirst(request, esPublica));
    }
});

async function cacheFirst(request) {
    const guardada = await caches.match(request);
    if (guardada) return guardada;

    const respuesta = await fetch(request);
    if (respuesta.ok) {
        const cache = await caches.open(ESTATICOS);
        cache.put(request, respuesta.clone());
    }
    return respuesta;
}

async function networkFirst(request, guardar) {
    try {
        const respuesta = await fetch(request);
        if (guardar && respuesta.ok) {
            const cache = await caches.open(PAGINAS);
            cache.put(new URL(request.url).pathname, respuesta.clone());
        }
        return respuesta;
    } catch (error) {
        const url = new URL(request.url);
        return (await caches.match(url.pathname)) || (await caches.match(OFFLINE));
    }
}
