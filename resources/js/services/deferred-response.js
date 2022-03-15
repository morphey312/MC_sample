import eventHub from '@/services/event-hub';

const promises = {};

const wait = (promiseId) => {
    return new Promise((resolve) => {
        promises[promiseId] = resolve;
    });
}

eventHub.$on('broadcast.deferred_response', (data) => {
    let promiseId = data.promiseId;
    if (promises[promiseId] !== undefined) {
        let resolve = promises[promiseId];
        delete promises[promiseId];
        axios.get('/api/v1/promise/' + promiseId).then(resolve);
    }
});

export default wait;