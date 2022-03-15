import moment from 'moment';
import handbook from '@/services/handbook';

const numberFormat = (value, precision = 2) => {
    return ((v) => isNaN(v) ? '' : v.toFixed(precision))(Number(value));
};

const hideNumberPhone = (value) => {
    if (_.isString(value) && value.length === 13) {
        return value.slice(0, 6) + '*****' + value.slice(11)
    }
    return null
}

const phoneNumberFormat = (value, separator = ' ', encloseCode = false) => {
    if (_.isString(value)) {
        if (value.length < 7) {
            return value;
        }

        let result = [
            value.substr(-7, 3),
            value.substr(-4, 2),
            value.substr(-2),
        ].join(separator);

        if (value.length >= 10) {
            result = (encloseCode ? '(' : '') + value.substr(-10, 3) + (encloseCode ? ') ' : ' ') + result;
        }

        if (value.length > 10) {
            result = value.substr(0, value.length - 10) + ' ' + result;
        }

        return result;
    }
    return null;
}

const dateFormat = (value, format = 'DD MMM YYYY') => {
    return value !== null ? moment(value).format(format) : '';
};

const timeFormat = (value, format = 'HH:mm') => {
    return dateFormat(value, format);
};

const datetimeFormat = (value, format = 'DD MMM YYYY, HH:mm') => {
    return dateFormat(value, format);
};

const daterangeFormat = (value, format = 'DD|MMM|YYYY', separator = '&mdash;') => {
    if (value) {
        let from = dateFormat(_.isObject(value) ? value.from : value[0], format);
        let to = dateFormat(_.isObject(value) ? value.to : value[1], format);

        if (!from && !to) {
            return null;
        }

        if (!from) {
            from = to;
        } else if (!to) {
            to = from;
        }

        let both = [], result = [];
        from = from.split('|');
        to = to.split('|');

        for (let i = from.length - 1; i >= 0; i--) {
            if (from[i] !== to[i]) {
                break;
            }
            both.unshift(from.pop());
            to.pop()
        }

        if (from.length !== 0) {
            result.push(from.join(' ') + separator + to.join(' '));
        }

        if (both.length !== 0) {
            result.push(both.join(' '));
        }

        return result.join(', ');
    }
    return null;
};

const durationFormat = (value, from = 'seconds') => {
    let duration = moment.duration(value, from);
    if (duration.asSeconds() > 0) {
        return [
            [duration.days(), __('дн.')],
            [duration.hours(), __('ч.')],
            [duration.minutes(), __('мин.')],
            [duration.seconds(), __('сек.')],
        ]
        .filter((component) => component[0] > 0)
        .map((component) => component[0] + ' ' + component[1])
        .join(', ');
    }
    return '';
};

const durationShortFormat = (value, from = 'seconds') => {
    let duration = moment.duration(value, from);
    if (duration.asSeconds() > 0) {
        if (duration.hours() > 0) {
            return moment.utc(duration.as('milliseconds')).format('H:mm:ss');
        }
        return moment.utc(duration.as('milliseconds')).format('m:ss');
    }
    return '';
};

const listFormat = (items = [], field = false, sortFunction = undefined) => {
    if (items instanceof Array) {
        let tempItems = items
            .map((item) => _.isNil(item) ? null : (field ? _.get(item, field) : item))
            .filter((item) => !_.isNil(item) && item !== '');

        if (sortFunction === true) {
            tempItems.sort();
        } else if (_.isFunction(sortFunction)) {
            tempItems.sort(sortFunction);
        }

        return tempItems.join(', ')
    }

    if (items instanceof Object) {
        return listFormat(Object.values(items), field);
    }

    return '';
};

const nameInitialsFormat = (val) => {
    return val ? val.split(/\s+/).map((part, index) => {
        return index === 0 ? part : part.substr(0, 1).toUpperCase();
    }).join(' ') : '';
}

const fromHandbook = (category, value) => {
    if (value instanceof Array) {
        return listFormat(value.map((v) => handbook.getOption(category, v)));
    }
    if (_.isVoid(value)) {
        return '';
    }

    if (typeof value === 'boolean') {
        value = Number(value);
    }

    return handbook.getOption(category, value);
}

const boolToString = (value, symbol = null) => {
    if (symbol !== null) {
        return value ? symbol : '';
    }
    return value ? __('Да') : __('Нет');
}

const boolFormat = (value, strict = true) => {
    if (strict && _.isVoid(value)) {
        return null;
    }
    return value ? __('Да') : __('Нет');
};

const timeTotal = (list, start, end) => {
    let total = 0;

    if(_.isEmpty(list)){
        return total;
    }

    list.forEach((item) => {
        total += moment(item[end], "HH:mm:ss").diff(moment(item[start], "HH:mm:ss"));
    });
    return moment.utc(total).format("HH:mm");
}

const makePlaceholderImage = (url, text = '', className = '') => {
    if(url.length === 0) {
        return '';
    }
    return '<img src="/svg/' + url + '" class="' + className + '" /> ' + text;
}

export {
    numberFormat,
    phoneNumberFormat,
    dateFormat,
    timeFormat,
    datetimeFormat,
    daterangeFormat,
    durationFormat,
    durationShortFormat,
    nameInitialsFormat,
    listFormat,
    boolToString,
    boolFormat,
    timeTotal,
    fromHandbook,
    makePlaceholderImage,
    hideNumberPhone,
};
