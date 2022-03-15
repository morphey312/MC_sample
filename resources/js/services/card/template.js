import translationServer from '@/services/translation';
import { keys } from 'lodash';

class Section
{
    /**
     * Constructor
     *
     * @param {string} label
     * @param {string} hint
     * @param {array} children
     */
    constructor(label, hint, children, isExtra = false) {
        this.label = translationServer.translate(label, {}, 'outpatient-card');
        this.hint = hint;
        this.children = children;
        this.isExtra = isExtra;
    }
}

class Line
{
    /**
     * Constructor
     *
     * @param {array} children
     */
    constructor(children) {
        this.children = children;
    }
}

class BaseField
{
    /**
     * Constructor
     *
     * @param {string} type
     * @param {number} key
     * @param {string} label
     * @param {array|false} options
     * @param {object} params
     */
    constructor(type, key, label, options, params) {
        this.type = type;
        this.key = key;
        this.label = translationServer.translate(label, {}, 'outpatient-card');
        this.options = _.isArray(options) ? options.map((opt) => ({
            key: opt.key,
            label: translationServer.translate(opt.label, {}, 'outpatient-card'),
        })) : options;
        this.bond = null;
        this.changed = false;
        this.touched = false;
        this.suffix = params.suffix ? translationServer.translate(params.suffix, {}, 'outpatient-card') : params.suffix;
    }

    /**
     * Bind field to model
     *
     * @param {object} model
     */
    bindTo(model) {
        this.bond = new Proxy(model, {
            set: (target, prop, val) => {
                if (target[prop] !== val) {
                    target[prop] = val;
                    this.touched = true;
                }
                return true;
            }
        });
    }

    /**
     * Set Changed Attribute
     *
     * @param isChanged boolean
     */
    setChanged(isChanged) {
        this.changed = isChanged;
    }

    /**
     * Set attribute to be untouched
     */
    untouch() {
        this.touched = false;
    }

    /**
     * Check if field is multiline
     *
     * @returns bool
     */
    isMultiline() {
        return false;
    }

    /**
     * Check if field has input element
     *
     * @returns bool
     */
    hasInput() {
        return false;
    }

    /**
     * Check if field has DS input
     *
     * @returns bool
     */
    hasDS() {
        return false;
    }

    /**
     * Check if field is Sketchpad
     *
     * @returns bool
     */
    isSketchpad() {
        return false;
    }
}

class Sketchpad extends BaseField
{
    /**
     * Constructor
     *
     * @param {string} type
     * @param {string} html
     * @param {number} key
     * @param {string} label
     * @param {array|false} options
     * @param {object} params
     */
    constructor(type, html, key, label, options, params) {
        super(type, key, label, options, params);
        this.html = html;
        this.height = params.height;
    }

    /**
     * @inheritdoc
     */
    isSketchpad() {
        return true;
    }
}

class Field extends BaseField
{
    /**
     * Constructor
     *
     * @param {string} type
     * @param {number} key
     * @param {string} label
     * @param {array|false} options
     * @param {object} params
     */
    constructor(type, key, label, options, params) {
        super(type, key, label, options, params);
        this.multiple = params.multiple;
        this.multiline = params.multiline;
        this.width = params.width;
        this.className = params.className;
    }

    /**
     * @inheritdoc
     */
    isMultiline() {
        return this.multiline;
    }

    /**
     * @inheritdoc
     */
    hasInput() {
        return this.type === 'input';
    }

    /**
     * @inheritdoc
     */
    hasDS() {
        return this.type === 'ds';
    }
}

class Structure
{
    /**
     * Constructor
     *
     * @param {array} structure
     * @param {object} fieldKeys
     * @param {array} fieldKeys
     */
    constructor(structure, fieldKeys, templateAddons = []) {
        this._fields = [];
        this._fieldKeys = this._getFieldKeys(fieldKeys, templateAddons);
        this._sections = this.prepare(structure, templateAddons);
    }

    /**
     * Prepare structure
     *
     * @param {array} structure
     * @param {array} templateAddons
     *
     * @returns {array}
     */
    prepare(structure, templateAddons = []) {
        let sections = [...this.mapStructure(structure)];
        templateAddons.forEach(addon => {
            sections = [...sections, ...this.mapStructure(addon.structure, true)];
        })
        return sections;
    }

    /**
     * Prepare structure
     *
     * @param {array} structure
     * @param {array} templateAddons
     *
     * @returns {array}
     */
    mapStructure(structure, isExtra = false) {
        return structure.map((node) => {
            if (_.isArray(node)) {
                return new Line(this.prepare(node));
            }

            if (node.isSketchpad) {
                let field = new Sketchpad(
                    node.type,
                    node.html,
                    this._fieldKeys['sketchpad'],
                    node.label,
                    node.options,
                    {
                        multiple: node.multiple === true,
                        multiline: node.multiline === true,
                        suffix: node.suffix,
                        width: node.width,
                        height: node.height,
                    }
                );
                this._fields.push(field);
                return field;
            }

            if (node.type !== undefined) {
                let field = new Field(
                    node.type,
                    this._fieldKeys[node.name],
                    node.label,
                    node.options,
                    {
                        multiple: node.multiple === true,
                        multiline: node.multiline === true,
                        suffix: node.suffix,
                        width: node.width,
                        className: node.className,
                    }
                );
                this._fields.push(field);
                return field;
            }

            return new Section(
                node.label,
                node.hint,
                this.prepare(node.children),
                isExtra
            );
        });
    }

    getSections() {
        return this._sections;
    }

    getFields() {
        return this._fields;
    }

    /**
     * Get template field keys full list
     * 
     * @param {object} fieldKeys 
     * @param {array} templateAddons 
     * 
     * @returns {object}
     */
    _getFieldKeys(fieldKeys, templateAddons = []) {
        let addonKeys = {};
        templateAddons.forEach(addon => {
            addonKeys = {...addonKeys, ...addon.field_keys};
        });
        return {...fieldKeys, ...addonKeys};
    }
}

export {Structure, Section, Line, Field, Sketchpad};
