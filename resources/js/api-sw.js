const EXPIRES_AT = 'sw-expires-at';

const ifNoQuery = (request) => {
    let q = (new URL(request.url)).searchParams.get('filters[query]');
    return q === '' || q === null || q === undefined;
}

const ifNoSelectedServices = (request) => {
    let url = new URL(request.url);
    return !url.searchParams.has('filters[or][0][id][0]');
}

const cacheConfig = [
    {
        name: 'handbook',
        pattern: '/api/v1/handbook',
    },
    {
        name: 'user',
        pattern: '/api/v1/permissions/user',
    },
    {
        name: 'user',
        pattern: '/api/v1/employees/webrtc/settings',
    },
    {
        name: 'clinics',
        pattern: '/api/v1/clinics/all',
    },
    {
        name: 'clinic-groups',
        pattern: '/api/v1/clinics/groups/all',
    },
    {
        name: 'msp',
        pattern: '/api/v1/msp/all',
    },
    {
        name: 'countries',
        pattern: '/api/v1/countries/all',
    },
    {
        name: 'roles',
        pattern: '/api/v1/roles/all',
    },
    {
        name: 'permissions',
        pattern: '/api/v1/permissions/all',
    },
    {
        name: 'specializations',
        pattern: '/api/v1/specializations/all',
    },
    {
        name: 'appointment-delete-reasons',
        pattern: '/api/v1/appointments/delete-reasons/all',
    },
    {
        name: 'appointment-statuses',
        pattern: '/api/v1/appointments/statuses/all',
    },
    {
        name: 'appointment-status-reasons',
        pattern: '/api/v1/appointments/statuses/reasons/all',
    },
    {
        name: 'call-request-purposes',
        pattern: '/api/v1/call-requests/purposes/all',
    },
    {
        name: 'payment-destinations',
        pattern: '/api/v1/services/payment-destinations/all',
    },
    {
        name: 'card-record-templates',
        pattern: '/api/v1/patients/cards/record-templates/all',
    },
    {
        name: 'notification-channels',
        pattern: '/api/v1/notifications/channels/all',
    },
    {
        name: 'positions',
        pattern: '/api/v1/employees/positions/all',
    },
    {
        name: 'ehealth-positions',
        pattern: '/api/v1/ehealth/positions/all',
    },
    {
        name: 'speciality-types',
        pattern: '/api/v1/employees/speciality-types/all',
    },
    {
        name: 'call-results',
        pattern: '/api/v1/calls/results/all',
    },
    {
        name: 'call-delete-reasons',
        pattern: '/api/v1/calls/delete-reasons/all',
    },
    {
        name: 'laboratories',
        pattern: '/api/v1/analysis/laboratories/all',
    },
    {
        name: 'money-recievers',
        pattern: '/api/v1/clinics/money-recievers/all',
    },
    {
        name: 'time-block-reasons',
        pattern: '/api/v1/day-sheets/time-block-reasons/all',
    },
    {
        name: 'employees',
        pattern: '/api/v1/employees/all',
        condition: ifNoQuery,
    },
    {
        name: 'information-sources',
        pattern: '/api/v1/patients/information-sources/all',
        condition: ifNoQuery,
    },
    {
        name: 'appointment-services',
        pattern: '/api/v1/services/appointment_list',
        condition: ifNoSelectedServices,
    },
    {
        name: 'static',
        pattern: new RegExp('^/(audio|css|fonts|js|svg|vendor)/'),
        expires: 604800000, // 1 week
    },
];

const actions = {
    bustcache: (keys) => {
        return caches.keys().then((list) => {
            return Promise.all(list.map((key) => {
                if (testCacheKey(key, keys)) {
                    return caches.delete(key);
                }
            }));
        }).then(() => {
            return true;
        });
    },
    bustExpiredCache: () => {
        return caches.open('static').then((cache) => {
            return cache.keys().then((key) => {
                return Promise.all(key.map((request) => {
                    return cache.match(request).then((response) => {
                        if (isOutdated(response)) {
                            return cache.delete(request);
                        }
                    });
                }));
            });
        });
    }
};

const isUrlMatch = (url, pattern) => {
    let urlObject = new URL(url);
    if (pattern instanceof RegExp) {
        return pattern.test(urlObject.pathname);
    }
    return pattern === urlObject.pathname;
}

const getCacheConf = (url) => {
    for (let conf of cacheConfig) {
        if (isUrlMatch(url, conf.pattern)) {
            return {
                request: (event) => event.request,
                response: (original) => original,
                condition: () => true,
                expires: false,
                ...conf,
            };
        }
    }
    return null;
}

const testCacheKey = (key, set) => {
    if (set === null) {
        return true;
    }
    if (Array.isArray(set)) {
        return set.indexOf(key) !== -1;
    }
    if (set.except !== undefined) {
        return !testCacheKey(key, set.except);
    }
    return key === set;
}

const cloneHeaders = (original) => {
    let headers = new Headers();
    for (let kv of original.entries()) {
        headers.append(kv[0], kv[1]);
    }
    return headers;
}

const cloneResponse = (original) => {
    let headers = cloneHeaders(original.headers);
    return original.clone().blob().then((blob) => {
        return new Response(blob, {
            status: original.status,
            statusText: original.statusText,
            headers: headers
        });
    });
}

const isOutdated = (response) => {
    if (response.headers.has(EXPIRES_AT)) {
        let expiresAt = new Date(response.headers.get(EXPIRES_AT));
        return expiresAt.getTime() < Date.now();
    }
    return false;
}

self.addEventListener('fetch', (event) => {
    let conf = getCacheConf(event.request.url);

    if (conf === null || !conf.condition(event.request)) {
        event.respondWith(
            fetch(event.request)
        );
    } else {
        let request = conf.request(event);
        event.respondWith(caches.match(request).then((response) => {
            if (response !== undefined) {
                // console.log('Got from cache: ' + request.url);
                return conf.response(response, event);
            } else {
                return fetch(request).then((response) => {
                    if (response.status >= 400) {
                        return response;
                    }
                    return cloneResponse(response).then((copy) => {
                        if (conf.expires !== false) {
                            copy.headers.set(EXPIRES_AT, (new Date(Date.now() + conf.expires)).toUTCString());
                        }
                        caches.open(conf.name).then((cache) => {
                            // console.log('Put to cache: ' + request.url);
                            cache.put(request, copy);
                        });
                        return conf.response(response, event);
                    });
                });
            }
        }));
    }
});

self.addEventListener('message', (event) => {
    if (event.data.action && actions[event.data.action] !== undefined) {
        actions[event.data.action](event.data.params).then((res) => {
            event.ports[0].postMessage(res);
        });
    } else {
        event.ports[0].postMessage(false);
    }
});