const clients = [];

const makeSafe = (component, message, condition = null) => {
    clients.push({component, message, condition});
}

const makeUnsafe = (component) => {
    _.remove(clients, (client) => client.component === component);
}

const makeUnsafeAll = () => {
    clients.splice(0);
}

const getCloseWarning = () => {
    for (let client of clients) {
        if (client.condition === null || client.condition() === true) {
            return _.isFunction(client.message) ? client.message() : client.message;
        }
    }
    return null;
}

window.onbeforeunload = (e) => getCloseWarning();

export {
    makeSafe,
    makeUnsafe,
    makeUnsafeAll,
    getCloseWarning,
};