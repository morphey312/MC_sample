import lts from '@/services/lts';

const NO_CONTEXT = '_';

const getStr = function() {
    return String(this);
}

const getProp = function(params) {
    return (this in params) ? String(params[this]) : '';
}

const getPropPlural = function(params) {
    let val = (this[0] in params) ? Number(params[this[0]]) : 1;
    if (this.length === 4) {
        let rest = val % 10;
        if ((val >= 10 && val <= 20) || rest === 0 || rest >= 5) {
            return String(this[3]);
        }
        if (rest === 1) {
            return String(this[1]);
        }
        return String(this[2]);
    }
    if (val === 1) {
        return String(this[1]);
    }
    return String(this[2]);
}

class TranslationServer
{
    /**
     * Constructor
     *
     * @param {string} lang
     * @param {string} original
     */
    constructor(lang = 'ua', original = false) {
        this._lang = lang;
        this._original = original;
        this._trans = {};
        this._originals = {};
        this._changed = () => {};
    }

    /**
     * Register translations
     *
     * @param {string} lang
     * @param {object} strings
     * @param {string} context
     */
    register(lang, strings, context = NO_CONTEXT) {
        if (this._trans[lang] === undefined) {
            this._trans[lang] = {};
        }
        if (this._trans[lang][context] === undefined) {
            this._trans[lang][context] = {};
        }
        let target = this._trans[lang][context];
        for (let key in strings) {
            target[key] = this.compile(strings[key]);
        }
    }

    /**
     * Compile translation string
     *
     * @param {string} str
     *
     * @returns {string|function}
     */
    compile(str) {
        let parts = str.split(/([{][^{}]+[}])/);
        if (parts.length === 1) {
            return parts[0];
        }
        let products = parts.map((part) => this.createProduct(part));
        return (params) => {
            return products.map((product) => product(params)).join('');
        }
    }

    /**
     * Create string product
     *
     * @param {string} part
     *
     * @returns {function}
     */
    createProduct(part) {
        if (part.substr(0, 1) === '{') {
            let expr = part.substr(1, part.length - 2);
            if (expr.indexOf('|') === -1) {
                return getProp.bind(expr);
            }
            let cases = expr.split('|');
            return getPropPlural.bind(cases);
        } else {
            return getStr.bind(part);
        }
    }

    /**
     * Translate the given key
     *
     * @param {string} key
     * @param {*} params
     * @param {string} context
     *
     * @returns {string}
     */
    translate(key, params = {}, context = NO_CONTEXT) {
        let raw = this.getRaw(key, this._lang, context);
        if (typeof raw === 'function') {
            return raw(params);
        }
        return raw;
    }

    /**
     * Get raw translation string
     *
     * @param {string} key
     * @param {string} lang
     * @param {string} context
     *
     * @returns {string}
     */
    getRaw(key, lang, context) {
        if (lang !== this._original && this._trans[lang] !== undefined) {
            let strings = this._trans[lang][context];
            if (strings !== undefined && strings[key] !== undefined) {
                return strings[key];
            }
            if (context !== NO_CONTEXT) {
                strings = this._trans[lang][NO_CONTEXT];
                if (strings !== undefined && strings[key] !== undefined) {
                    return strings[key];
                }
            }
        }
        if (this._originals[key] !== undefined) {
            return this._originals[key];
        }
        return this._originals[key] = this.compile(key);
    }

    /**
     * Set lang change callback
     *
     * @param {function} callback
     */
    changed(callback) {
        let chain = this._changed;
        this._changed = (val) => {
            chain(val);
            callback(val);
        }
    }

    /**
     * Get current language
     *
     * @returns {string}
     */
    get lang() {
        return this._lang;
    }

    /**
     * Get current language ISO
     * 
     * @returns {string}
     */
    get isoLang() {
        switch (this._lang) {
            case 'ua': return 'uk';
            default: return this._lang;
        }
    }

    /**
     * Set current language
     *
     * @param {string} val
     */
    set lang(val) {
        if (this._lang !== val) {
            this._lang = val;
            this._changed(val);
        }
    }
}

if (lts.language === undefined) {
    lts.language = 'ua';
}

export default new TranslationServer(lts.language || 'ua', 'ru');
