const _ = window._ = require('lodash');

_.isFilled = (value) => {
    return !_.isVoid(value);
}

_.isVoid = (value) => {
    return value === '' || value === null || value === undefined || (Array.isArray(value) && value.length === 0);
}

_.findById = (items, id) => {
    return _.find(items, (item) => Number(item.id) === Number(id));
}

_.onlyFilled = (object) => {
    let result = {};
    for (let prop in object) {
        if (_.isFilled(object[prop])) {
            result[prop] = object[prop];
        }
    }
    return result;
}

_.waitUntil = (condition, interval = 100, timeout = -1) => {
    return new Promise((resolve, reject) => {
        let time = 0;
        const checkFunction = () => {
            if (condition()) {
                resolve();
            } else {
                if (timeout === -1 || time < timeout) {
                    time += interval;
                    setTimeout(checkFunction, interval);
                } else {
                    reject();
                }
            }
        }
        _.defer(checkFunction);
    });
}

_.wrapArray = (v) => {
    if (_.isArray(v)) {
        return v;
    }
    if (v === null || v === undefined) {
        return [];
    }
    return [v];
}

_.sumOf = (items, prop) => {
    return items.reduce(
        (sum, val) => isNaN(val[prop]) ? sum : sum + Number(val[prop]),
        0
    );
}
