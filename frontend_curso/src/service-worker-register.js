export default function registerServiceWorker(){
  navigator.serviceWorker.register('/service-worker.js')
    .then(() => console.log('Service worker registered'))
    .catch(err => console.warn('SW registration failed', err));
}
