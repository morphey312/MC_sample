import {Model} from 'vue-mc';
import {ruleRequiresValue} from '@/services/validation';

export default {
    props: {
        entity: {
            type: Object,
            required: true,
        },
        label: {
            type: String,
        },
        property: {
            type: String,
            required: true,
        },
        cssClass: {
            type: String,
            default: 'form-input',
        },
        controlSize: {
            type: String,
            default: 'medium',
        },
        errorPrefix: {
            type: String,
            default: ''
        },
        tabIndex: {
            type: Number,
            default: 0
        },
        disabled: {
            type: Boolean,
            default: false
        },
        readonly: {
            type: Boolean,
            default: false
        },
        clearable: {
            type: Boolean,
            default: false,
        },
        required: {
            type: Boolean,
            default: null,
        },
        placeholder: {
            type: [Boolean, String],
            default: true,
        },
        autocomplete: {
            type: String,
            default: 'chrome-off',
        },
    },
    computed: {
        isRequired() {
            return this.required === null ? this.detectIsRequired() : this.required;
        },
    },
    data() {
        return {
            model: this.frontToBack(this.deepGet(this.entity, this.property)),
        };
    },
    mounted() {
        this.$watch('$props.entity.' + this.property, (val) => {
            this.model = this.frontToBack(val);
        });
        this.$watch('$data.model', (val) => {
            this.deepSet(this.entity, this.property, this.backToFront(val));
        });

        let inputs = this.$el.querySelectorAll('input');

        if(inputs.length){
            inputs.forEach((input) => {
                input.autocomplete = this.autocomplete;
            });
        }
    },
    methods: {
        frontToBack(val) {
            return val;
        },
        backToFront(val) {
            return val;
        },
        deepGet(model, path) {
            let components = path.split('.');
            for (let component of components) {
                model = this.propGet(model, component);
            }
            return model;
        },
        deepSet(model, path, value) {
            let components = path.split('.');
            let key = components.pop();
            for (let component of components) {
                model = this.propGet(model, component);
            }
            if (model !== undefined) {
                this.propSet(model, key, value);
            }
        },
        propGet(object, key) {
            if (object instanceof Model) {
                if (object.has(key)) {
                    return object.get(key);
                }
            } else if (_.isObject(object)) {
                if (key in object) {
                    return object[key];
                }
            }
            return undefined;
        },
        propSet(object, key, value) {
            if (object instanceof Model) {
                object.set(key, value);
            } else if (_.isObject(object)) {
                object[key] = value;
            }
        },
        detectIsRequired() {
            let entity, property;
            if (this.property.indexOf('.') !== -1) {
                let components = this.property.split('.');
                property = components.pop();
                entity = this.propGet(this.entity, components.join('.'));
            } else {
                entity = this.entity;
                property = this.property;
            }
            if (entity instanceof Model) {
                return entity.getValidateRules(property)
                    .some((rule) => ruleRequiresValue(rule));
            }
            return null;
        },
        changed() {
            this.$emit('changed', this.model);
        },
        input() {
            this.$emit('input', this.model);
        },
    },
};
