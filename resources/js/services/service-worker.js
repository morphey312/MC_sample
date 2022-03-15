if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/api-sw.js')
        .then((registration) => {
            postAction('bustExpiredCache');
        })
        .catch((error) => {
            console.log('API service worker registration failed with ' + error);
        });
}

const postMessage = ('serviceWorker' in navigator) 
    ? (data) => {
        return new Promise((resolve, reject) => {
            let channel = new MessageChannel();
            channel.port1.onmessage = (event) => {
                if (event.data.error) {
                    reject(event.data.error);
                } else {
                    resolve(event.data);
                }
            };
            navigator.serviceWorker.ready.then(() => {
                if (navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage(data, [channel.port2]);
                }
            });
        });
    }
    : () => {
        return Promise.resolve(false);
    };

const postAction = (action, params = {}) => {
    return postMessage({action, params});
}

const bustCache = (name = null) => {
    return postAction('bustcache', name);
}

const bustLocalizedCache = () => {
    return bustCache([
        'handbook',
        'specializations',
        'appointment-delete-reasons',
        'appointment-statuses',
        'appointment-status-reasons',
        'call-request-purposes',
        'payment-destinations',
        'call-results',
        'call-delete-reasons',
        'information-sources',
        'time-block-reasons',
        // Other handbooks, which aren't localized yet 
        // 'clinics',
        // 'clinic-groups',
        // 'countries',
        // 'roles',
        // 'permissions',
        // 'positions',
    ]);
}

export {
    postMessage,
    postAction,
    bustCache,
    bustLocalizedCache,
};