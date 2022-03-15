import {Model} from 'vue-mc';
import eventHub from '@/services/event-hub';
import store from '@/store';
import translationServer from '@/services/translation';
const qs = require('qs');

const watchedModels = [];
let userLocaleSuffix = undefined;

translationServer.changed(() => {
    userLocaleSuffix = undefined;
});

const getLocaleSuffix = () => {
    if (userLocaleSuffix === undefined) {
        let locales = store.state.user.locales;
        let lang = translationServer.lang;
        userLocaleSuffix = null;
        if (lang in locales) {
            userLocaleSuffix = locales[lang].suffix;
        }
    }
    return userLocaleSuffix;
}

eventHub.$on('broadcast.record_changed', (data) => {
    if (data.data.user !== store.state.user.id && watchedModels.length !== 0) {
        let className = (data.data.saved || data.data.deleted).split('\\').join('_');
        watchedModels.forEach((model) => {
            if (model.id === data.data.attributes.id && model.getModelClass() === className) {
                model.changedExternally = true;
            }
        });
    }
});

class BaseModel extends Model
{
    /** 
     * @inheritdoc
     */
    constructor(attributes = {}, collection = null, options = {}) {
        super(attributes, collection, options);
        this.on('create', (event) => {
            if (!event.error) {
                eventHub.$emit('created:' + this.getModelClass(), this);
            }
        });
        this.on('update', (event) => {
            if (!event.error) {
                eventHub.$emit('updated:' + this.getModelClass(), this);
            }
        });
        this.on('delete', (event) => {
            if (!event.error) {
                eventHub.$emit('deleted:' + this.getModelClass(), this);
            }
        });
        this._parent = null;
        this._exChanged = false;
        this._scopes = null;
    }
    
    /**
     * Get class name the instance 
     * 
     * @returns {string}
     */ 
    getModelClass() {
        return this.constructor.name;
    }

    /** 
     * @inheritdoc
     */
    getDefaultMethods() {
        return {
            fetch:  'GET',
            save:   'POST',
            update: 'PUT',
            create: 'POST',
            patch:  'PATCH',
            delete: 'DELETE',
        }
    }
    
    /** 
     * @inheritdoc
     */
    options() {
        return {
            validateOnChange: false,
            validateOnSave: true,
            validateRecursively: false,
            safeSubmit: true,
        };
    }
    
    /**
     * Perform validation on nested model
     * 
     * @param {Model} model
     * @param {function} when
     * @param {*} attributes
     * 
     * @return {Promise}
     */
    validateSubmodel(model, when = null, attributes = undefined) {
        if (model instanceof Model) {
            if (!_.isFunction(when) || when(model) !== false) {
                return model.validate(attributes).then((errors) => {
                    return _.isEmpty(errors) ? false : errors;
                });
            }
        }
        return Promise.resolve(false);
    }

    /**
     * Perform validation on nested models array
     *
     * @param {Model} model
     *
     * @return {Promise}
     */
    validateModelsArray(items) {
        let validations = items.map((model) => this.validateSubmodel(model));

        return Promise.all(validations).then((result) => {
            let errors = {};

            _.forEach(result, (e, key) => {
                if(e !== false) {
                    return errors['e' + key] = [e];
                }
            });

            if(_.isEmpty(errors)){
                return false;
            }

            return errors;
        });
    }
    
    /**
     * Cast value to model instance
     * 
     * @param {*} className
     * @param {*} value
     * @param {bool} allowNull
     * 
     * @returns {*}
     */
    castToInstance(className, value, allowNull = false) {
        if (value instanceof className) {
            return value;
        }
        if (value == null && allowNull) {
            return null;
        }
        return new className(value || {});
    }
    
    /**
     * Cast values to model instances
     * 
     * @param {*} className
     * @param {array} values
     * 
     * @returns {array}
     */
    castToInstances(className, values) {
        if (_.isArray(values)) {
            return values.map(v => this.castToInstance(className, v));
        }
        return [];
    }
    
    /**
     * Cast value to array
     * 
     * @param {*} value
     * 
     * @returns {array}
     */ 
    castToArray(value) {
        if (_.isArray(value)) {
            return value;
        }
        return _.isNil(value) ? [] : [value];
    }
    
    /**
     * Init nested model
     * 
     * @param {*} className
     * @param {*} value
     * 
     * @returns {*}
     */ 
    initSubModel(className, value) {
        let instance = this.castToInstance(className, value);
        instance.setParent(this);
        return instance;
    }
    
    /** 
     * @inheritdoc
     */
    getSaveData() {
        let attributes = super.getSaveData();
        if (this.getOption('safeSubmit')) {
            attributes = _.pick(attributes, Object.keys(this.defaults()));
        }
        return attributes;
    }
    
    /** 
     * @inheritdoc
     */
    onSaveSuccess(response) {
        let isNew = this.isNew();
        super.onSaveSuccess(response); 
        if (isNew) {
            this.emit('create', {error: null});
        } else {
            this.emit('update', {error: null});
        }
    }
    
    /** 
     * Fetch model data
     * 
     * @param {array} scopes
     * @param {object} options
     */
    fetch(scopes = null, options = {}) {
        if (scopes !== null) {
            this.withScopes(scopes);
        }
        return super.fetch(options);
    }
    
    /**
     * Set parent model
     * 
     * @param {mixed} model
     */ 
    setParent(model) {
        this._parent = model;
    }

    /**
     * Get url with query params
     * 
     * @param {string} route 
     * @param {object} params 
     * @param {object} filters
     * 
     * @return string 
     */
    getUrlWithQueryParams(route, params, filters = null, scopes = null, queryParams) {
        return this.getURL(route, params) + '?' + 
                qs.stringify({
                    filters, 
                    ...this.getScopes(scopes),
                    ...queryParams,
                });
    }

    /**
     * Get value for scopes
     * 
     * @param {array} scopes
     * 
     * @returns {object}
     */ 
    getScopes(scopes) {
        if (scopes !== null) {
            return {scopes};
        }
        return {};
    }

    /**
     * Use these scopes while fetching model
     * 
     * @param {array} scopes 
     */
    withScopes(scopes) {
        this._scopes = scopes;
        return this;
    }

    /**
     * @inheritdoc
     */
    getFetchQuery() {
        return {
            ...this.getScopes(this._scopes)
        };
    }

    /**
     * @inheritdoc
     */
    getSaveQuery() {
        return {
            ...this.getScopes(this._scopes)
        };
    }

    /**
     * Let localized value of attribute
     * 
     * @param {String} attribute
     * 
     * @returns {String}
     */
    getAttributeI18N(attribute) {
        let suffix = getLocaleSuffix();
        if (suffix !== null) {
            let i18nAttribute = attribute + '_' + suffix;
            if (this.has(i18nAttribute)) {
                let localized = this.get(i18nAttribute);
                if (_.isFilled(localized)) {
                    return localized;
                }
            }
        }
        return this.get(attribute);
    }
    
    /**
     * Watch when model is changed externally
     */ 
    watchExChanges() {
        if (watchedModels.indexOf(this) === -1) {
            watchedModels.push(this);
        }
    }
    
    /**
     * Stop watching when model is changed externally
     */ 
    stopWatchExChanges() {
        let index = watchedModels.indexOf(this);
        if (index !== -1) {
            watchedModels.splice(index, 1);
        }
    }
    
    /**
     * Check if model was changed externally
     */ 
    get changedExternally() {
        return this._exChanged;
    }
    
    /**
     * Mark that model was changed externally
     */ 
    set changedExternally(val) {
        if (val) {
            if (!this._exChanged) {
                this.emit('changedExternally');
            }
            this._exChanged = true;
        } else {
            this._exChanged = false;
        }
    }
}

export default BaseModel;