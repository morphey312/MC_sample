import {
    boolean,
    before,
    after,
    rule,
    messages,
    required,
    date,
    string,
    email,
    length,
    dateformat,
    integer,
    gt,
    gte,
    lt,
    lte,
    numeric,
    equals,
    not,
} from 'vue-mc/validation';

import translationServer from '@/services/translation';

const STRING_MAX_LEN = 255;
const TEXT_MAX_LEN = 65535;
const PASSWORD_MIN_LEN = 8;
const PHONE_NUMBER_RE = new RegExp('^[+]?([0-9]{10,14}|[0-9]{3,5})$');
const PHONE_NUMBER_RE_CELLULAR = new RegExp('^[+]?([0-9]{10,14})$');
const EDRPOU_RE = new RegExp('^[0-9]{8}$');
const IBAN_RE = new RegExp('^[A-Z]{2}(?:[ ]?[0-9]){27}$');
const TAX_ID_RE = new RegExp('^[0-9]{10}$');
const UNZR_RE = new RegExp('^[0-9]{8}-[0-9]{5}$');
const ZIP_RE = new RegExp('^[0-9]{5}$');
const DIGITS_RE = new RegExp('[0-9]');
const UPPERCASE_RE = new RegExp('[A-Z]');
const LOWERCASE_RE = new RegExp('[a-z]');
const UKR_SPELLING_RE = new RegExp('^(?!.*[ЫЪЭЁыъэё@$^#])[a-zA-ZА-ЯҐЇІЄа-яґїіє0-9№\\s"!^*)\\]\\[(&._,:;+-]*$');
const EHEALTH_CONTRACT_NUMBER_RE = new RegExp('^\\d{4}-[\\dAEHKMPTX]{4}-[\\dAEHKMPTX]{4}$');
const MFO_RE = new RegExp('^[0-9]{6}$');

const __ = (string, args = {}) => {
    return translationServer.translate(string, args);
};

const attributeEquals = (attribute, attributeValue) => {
    return rule({
        name: 'attributeEquals',
        data: {attribute, attributeValue},
        test: (value, field, model) => {
            return _.isEqual(model.get(attribute), attributeValue);
        },
    });
}

const greaterThanAttribute = (attribute) => {
    return rule({
        name: 'greaterThanAttribute',
        data: {attribute},
        test: (value, field, model) => {
            return value > model.get(attribute);
        },
    });
}

const missing = rule({
    name: 'missing',
    test: (value) => {
        return !_.isFilled(value);
    },
});

const maxlen = (max) => {
    return rule({
        name: 'maxlen',
        test: (value) => {
            return _.size(value) <= max;
        },
        data: {max},
    });
}

const phoneNumber = rule({
    name: 'phoneNumber',
    test: (value) => {
        return PHONE_NUMBER_RE.test(value);
    },
});

const phoneNumberCellular = rule({
    name: 'phoneNumber',
    test: (value) => {
        return PHONE_NUMBER_RE_CELLULAR.test(value);
    },
});

const edrpou = rule({
    name: 'edrpou',
    test: (value) => {
        return EDRPOU_RE.test(value);
    },
});
const iban = rule({
    name: 'iban',
    test: (value) => {
        return IBAN_RE.test(value);
    },
});

const taxId = rule({
    name: 'taxId',
    test: (value) => {
        return TAX_ID_RE.test(value);
    },
});

const unzr = rule({
    name: 'taxId',
    test: (value) => {
        return UNZR_RE.test(value);
    },
});

const zip = rule({
    name: 'zip',
    test: (value) => {
        return ZIP_RE.test(value);
    },
});

const ehealthContractNumber = rule({
    name: 'ehealthContractNumber',
    test: (value) => {
        return EHEALTH_CONTRACT_NUMBER_RE.test(value);
    },
});

const mfo = rule({
    name: 'mfo',
    test: (value) => {
        return MFO_RE.test(value);
    },
});

const modelExists = rule({
    name: 'modelExists',
    test: (value, attribute, model) => {
        return !model.isNew();
    },
});

const modelNotExists = rule({
    name: 'modelNotExists',
    test: (value, attribute, model) => {
        return model.isNew();
    },
});
const dependsOn = (attribute) => rule({
    name: 'dependsOn',
    test: (value, field, model) => {
        let attributeValue = model.get(attribute);
        if(!value && attributeValue){
            return false;
        }
        return true;
    },
});


const requiredArray = rule({
    name: 'requiredArray',
    test: (value) => {
        return _.isArray(value) && _.size(value) > 0;
    },
});

const password = rule({
    name: 'passwordLength',
    test: (value) => {
        return _.size(value) >= PASSWORD_MIN_LEN;
    },
})
.and(rule({
    name: 'passwordDigits',
    test: (value) => {
        return DIGITS_RE.test(value);
    },
})).and(rule({
    name: 'passwordUppercase',
    test: (value) => {
        return UPPERCASE_RE.test(value);
    },
})).and(rule({
    name: 'passwordLowercase',
    test: (value) => {
        return LOWERCASE_RE.test(value);
    },
}));

const attributeMissing = (attribute) => rule({
    name: 'attributeMissing',
    test: (value, field, model) => {
        let attributeValue = model.get(attribute);
        return !_.isFilled(attributeValue);
    },
});

const assertion = (predicate) => rule({
    name: 'assertion',
    test: (value, field, model) => {
        return predicate(value, field, model);
    },
});

const ukrSpelling = rule({
    name: 'ukrSpelling',
    test: (value) => {
        return UKR_SPELLING_RE.test(value);
    },
});

const ruleRequiresValue = (rule) => {
    return rule._is_required === true
        || [
            ...(rule._and || []),
            ...(rule._or || [])
        ].some((r) => ruleRequiresValue(r));
}

const setValueIsRequired = (rule) => {
    let baseCopy = rule.copy;
    rule._is_required = true;
    rule.copy = () => {
        let copy = baseCopy.call(rule);
        copy._is_required = true;
        copy.copy = rule.copy;
        return copy;
    }
}

messages.set('before', __('Пожалуйста, укажите более раннюю дату'));
messages.set('after', __('Пожалуйста, укажите более позднюю дату чем'));
messages.set('email', __('Пожалуйста, введите корректный e-mail'));
messages.set('required', __('Пожалуйста, заполните это поле'));
messages.set('missing', __('Вы должны оставить это поле пустым'));
messages.set('maxlen', __('Длина значения не должна превышать {max} символов', {max: '${max}'}));
messages.set('phoneNumber', __('Укажите номер телефона в корректном формате'));
messages.set('edrpou', __('Укажите код ЕГРПОУ в корректном формате'));
messages.set('taxId', __('Укажите РНУКПН в корректном формате'));
messages.set('unzr', __('Укажите УНЗР в корректном формате'));
messages.set('zip', __('Укажите почтовый индекс в корректном формате'));
messages.set('ehealthContractNumber', __('Укажите номер договора в корректном формате'));
messages.set('mfo', __('Укажите МФО в корректном формате'));
messages.set('date', __('Укажите дату в корректном формате'));
messages.set('attributeEquals', __('Значение атрибута не совпадает'));
messages.set('modelExists', __('Укажите значение'));
messages.set('modelNotExists', __('Укажите значение'));
messages.set('attributeMissing', __('Укажите значение'));
messages.set('requiredArray', __('Пожалуйста, выберите хотя бы одно значение из списка'));
messages.set('dependsOn', __('Укажите значение'));
messages.set('passwordLength', __('Пароль должен состоять как минимум из {len} символов', {len: PASSWORD_MIN_LEN}));
messages.set('passwordDigits', __('Пароль должен содержать хотя бы одну цифру'));
messages.set('passwordUppercase', __('Пароль должен содержать хотя бы одну латинскую букву в верхнем регистре'));
messages.set('passwordLowercase', __('Пароль должен содержать хотя бы одну латинскую букву в нижнем регистре'));
messages.set('integer', __('Укажите числовое значение'));
messages.set('numeric', __('Укажите числовое значение'));
messages.set('gt', __('Укажите число большее, чем {min}', {min: '${min}'}));
messages.set('gte', __('Укажите число не меньшее, чем {min}', {min: '${min}'}));
messages.set('assertion', __('Введенные данные не соответствуют критерию'));
messages.set('ukrSpelling', __('Данные должны вноситься на украинском языке'));

setValueIsRequired(required);
setValueIsRequired(requiredArray);

export {
    boolean,
    before,
    after,
    required,
    date,
    string,
    email,
    dateformat,
    integer,
    gt,
    gte,
    lt,
    lte,
    numeric,
    length,
    not,
    attributeEquals,
    missing,
    maxlen,
    phoneNumber,
    phoneNumberCellular,
    edrpou,
    taxId,
    iban,
    zip,
    ukrSpelling,
    ehealthContractNumber,
    mfo,
    password,
    requiredArray,
    modelExists,
    modelNotExists,
    greaterThanAttribute,
    attributeMissing,
    assertion,
    equals,
    STRING_MAX_LEN,
    TEXT_MAX_LEN,
    ruleRequiresValue,
    dependsOn,
    unzr
};
