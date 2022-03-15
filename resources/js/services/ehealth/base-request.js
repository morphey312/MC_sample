const FULL_PHONE_NUMBER_RE = new RegExp('^[+]38[0-9]{10}$');
const SHORT_PHONE_NUMBER_RE = new RegExp('^[0-9]{10}$');

class BaseRequest
{
    constructor(subject) {
        this._subject = subject;
        this._data = {};
        this._propmakers = [];
        this._errors = [];
    }

    addProp(prop, maker, validation = false, alias = false) {
        this._propmakers.push({prop, maker, validation, alias});
    }

    transform() {
        return this.makeProps(this._propmakers, this._data);
    }

    makeProps(propmakers, container) {
        return Promise.all(
            propmakers.map((pm) => this.makeProp(pm.prop, pm.maker, pm.validation, pm.alias))
        ).then((results) => {
            results.forEach((result) => {
                if (result.data !== null && result.data !== '') {
                    container[result.prop] = result.data;
                }
            });
        });
    }

    makeProp(prop, maker, validation, alias) {
        if (maker instanceof BaseRequest) {
            return maker.transform().then(() => {
                maker.getErrors().forEach((err) => {
                    if (alias === '') {
                        this.addError(err.prop, err.error);
                    } else {
                        this.addError(`${alias||prop}.${err.prop}`, err.error);
                    }
                });
                return {
                    prop,
                    data: maker.getData(),
                };
            });
        } else {
            let result = maker();
            if (!(result instanceof Promise)) {
                result = Promise.resolve(result);
            }
            return result.then((data) => {
                if (validation) {
                    return Promise.all(validation.map((rule) => {
                        return rule(data || '', prop, null);
                    })).then((errors) => {
                        errors.forEach((err) => {
                            if (typeof err === 'string') {
                                this.addError(alias || prop, err);
                            }
                        });
                        return {
                            prop, 
                            data,
                        };
                    });
                } else {
                    return {
                        prop, 
                        data,
                    };
                }
            });
        }
    }

    addError(prop, error) {
        this._errors.push({prop, error});
    }

    getData() {
        return this._data;
    }

    hasErrors() {
        return this._errors.length !== 0;
    }

    getErrors() {
        return this._errors;
    }

    getErrorsAsObject() {
        let map = {};
        this._errors.forEach((err) => {
            if (!(err.prop in map)) {
                map[err.prop] = [];
            }
            map[err.prop].push(err.error);
        });
        return map;
    }

    fromDict(val, dict) {
        if (_.isVoid(val)) {
            return null;
        }
        if (val in dict) {
            return dict[val];
        }
        throw new Error(`Value '${val}' does not present in dictionary`);
    }

    makePhones(phones) {
        let types = [];
        return Object.keys(phones).map((key) => {
            try {
                let number = this.normalizePhoneNumber(phones[key]);
                let type = this.isMobileNumber(number) ? 'MOBILE' : 'LAND_LINE';
                if (types.indexOf(type) === -1) {
                    types.push(type);
                } else {
                    this.addError(key, __('Не разрешается указывать телефоны одного типа'));
                }
                return {
                    type,
                    number, 
                }
            } catch (e) {
                this.addError(key, __('Недопустимый формат номера'));
                return {number: null};
            }
        }).filter((num) => num.number);
    }

    normalizePhoneNumber(num) {
        if ((typeof num === 'string') && num !== '') {
            if (FULL_PHONE_NUMBER_RE.test(num)) {
                return num;
            }
            if (SHORT_PHONE_NUMBER_RE.test(num)) {
                return `+38${num}`;
            }
            throw new Error('Invalid phone number format');
        }
        return null;
    }

    isMobileNumber(num) {
        return typeof num === 'string' && [
            '+38039',
            '+38067',
            '+38068',
            '+38096',
            '+38097',
            '+38098',
            '+38050',
            '+38066',
            '+38095',
            '+38099',
            '+38063',
            '+38093',
            '+38091',
            '+38092',
            '+38094',
        ].indexOf(num.substr(0, 6)) !== -1;
    }
}

export default BaseRequest;