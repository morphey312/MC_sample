const EQUALS = 1;
const CONTAINS = 2;
const IN_ARRAY = 3;

const isEqual = (a, b) => {
    if (a instanceof Array) {
        return a.some((item) => isEqual(item, b));
    }
    if (typeof b === 'number') {
        return Number(a) === b;
    }
    return a.toLowerCase() === b;
}

const inArray = (a, b) => {
    return b.some((item) => isEqual(a, item));
}

const strContains = (a, b) => {
    return a.toLowerCase().indexOf(b) !== -1;
}

const forEachProp = (object, thisObject, func) => {
    for (let prop in object) {
        func.call(thisObject, prop, object[prop]);
    }
}

class CollectionFilter
{
    /**
     * Constructor
     */
    constructor() {
        this._wheres = [];
    }

    /**
     * Add criteria by full match
     *
     * @param {string|object} key
     * @param {*} value
     *
     * @returns {CollectionFilter}
     */
    where(key, value = null) {
        if (_.isObjectLike(key)) {
            forEachProp(key, this, this.where);
            return this;
        }
        if (_.isFilled(value)) {
            this._wheres.push({
                key,
                value: (typeof value === 'boolean' || typeof value === 'number')
                    ? Number(value)
                    : String(value).toLowerCase(),
                op: EQUALS,
            });
        }
        return this;
    }

    /**
     * Add criteria by partial match
     *
     * @param {string|object} key
     * @param {string} value
     *
     * @returns {CollectionFilter}
     */
    whereContains(key, value = null) {
        if (_.isObjectLike(key)) {
            forEachProp(key, this, this.whereContains);
            return this;
        }
        if (_.isFilled(value)) {
            this._wheres.push({
                key,
                value: String(value).toLowerCase(),
                op: CONTAINS,
            });
        }
        return this;
    }

    /**
     * Add criteria by matching the set
     *
     * @param {string|object} key
     * @param {array} value
     *
     * @returns {CollectionFilter}
     */
    whereIn(key, value = null) {
        if (_.isObjectLike(key)) {
            forEachProp(key, this, this.whereIn);
            return this;
        }
        if (_.isArray(value) && value.length !== 0) {
            this._wheres.push({
                key,
                value: value.map((item) => {
                    return (typeof item === 'boolean' || typeof item === 'number')
                        ? Number(item)
                        : String(item).toLowerCase()
                }),
                op: IN_ARRAY,
            });
        }
        return this;
    }

    /**
     * Filter an array
     *
     * @param {array} array
     * @param {boolean} some
     *
     * @returns {array}
     */
    filter(array, some = false) {
        if (this._wheres.length === 0) {
            return array;
        }
        return array.filter((item) => {
            let func = some ? this._wheres.some : this._wheres.every
            return func.call(this._wheres, (where) => {
                if (where.op === EQUALS) {
                    return isEqual(_.get(item, where.key), where.value);
                }
                if (where.op === CONTAINS) {
                    return strContains(_.get(item, where.key), where.value);
                }
                if (where.op === IN_ARRAY) {
                    return inArray(_.get(item, where.key), where.value);
                }
                return false;
            })
        });
    }
}

export default CollectionFilter;
