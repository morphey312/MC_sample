import BaseRecord from './base-record';
import RecordTemplate from './record-template';
import {
    maxlen,
    TEXT_MAX_LEN
} from '@/services/validation';

const NO_CHANGES = 'no_changes';

class OutpatientRecord extends BaseRecord
{
    /**
     * @inheritdoc
     */
    constructor(attributes = {}, collection = null, options = {}) {
        super(attributes, collection, options);
        this._previous = null;
        this._structure = null;
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            card_specialization_id: null,
            appointment_id: null,
            fields: [],
            diagnosis: null,
            diagnosis_icd: [],
            diagnosis_icd_names: [],
            complaints: null,
            template_id: null,
            diagnosis_erased: false
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            diagnosis: maxlen(TEXT_MAX_LEN),
            complaints: maxlen(TEXT_MAX_LEN),
        };
    }

    /**
     * @inheritdoc
     */
    mutations() {
        return {
            template: (value) => value === null ? null : this.castToInstance(RecordTemplate, value),
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/cards/outpatient-records',
            fetch: '/api/v1/patients/cards/outpatient-records/{id}',
            update: '/api/v1/patients/cards/outpatient-records/{id}',
        };
    }

    /**
     * Map fields to structure
     *
     * @param {object} structure
     */
    mapStructure(structure) {
        let fields = this.fields;
        let previousFields = this._previous.fields;

        this._structure = structure;
        structure.getFields().forEach((field) => {
            let bond = this.findField(fields, field.key);
            if (bond === undefined) {
                bond = this.newField(field.key);
                fields.push(bond);
            }

            if (field.multiple) {
                bond.option_value = this.multiValue(bond.option_value);
            } else {
                bond.option_value = this.soleValue(bond.option_value);
            }

            let prev = this.findField(previousFields, field.key);
            if (prev !== undefined && this.isValueChanged(prev, bond)) {
                field.setChanged(true);
            } else {
                field.setChanged(false);
            }

            field.untouch();
            field.bindTo(bond);
        });
    }

    /**
     * Create new empty field
     *
     * @param {number} field_id
     *
     * @returns {object}
     */
    newField(field_id) {
        return {
            field_id,
            value: null,
            option_value: null,
        };
    }

    /**
     * Cast value to array
     *
     * @param {*} val
     *
     * @returns {array}
     */
    multiValue(val) {
        if (_.isArray(val)) {
            return val;
        }
        if (null === val) {
            return [];
        }
        return [val];
    }

    /**
     * Cast value to scalar
     *
     * @param {*} val
     *
     * @returns {number|null}
     */
    soleValue(val) {
        if (_.isArray(val)) {
            return val[0];
        }
        return val;
    }

    /**
     * Set previous
     */
    setPrevious(prev = undefined) {
        if (prev === undefined) {
            prev = this._previous;
        } else {
            this._previous = prev;
        }
        if (prev.diagnosis !== null && this.diagnosis === null) {
            this.diagnosis = prev.diagnosis;
        }
        if (prev.diagnosis_icd.length !== 0 && this.diagnosis_icd.length === 0) {
            this.diagnosis_icd = [...prev.diagnosis_icd];
            this.diagnosis_icd_names = [...prev.diagnosis_icd_names];
        }
        if (prev.complaints !== null && this.complaints === null) {
            this.complaints = prev.complaints;
        }
        let fields = this.fields;
        prev.fields.forEach((field) => {
            let newField = this.findField(fields, field.field_id);
            if (newField === undefined) {
                newField = this.newField(field.field_id);
                newField.value = field.value;
                newField.option_value = field.option_value;
                fields.push(newField);
            }
        });
    }

    /**
     * Get data for save request
     * 
     * @param {bool} detectNoChanges
     * 
     * @returns {object}
     */
    getSaveData(detectNoChanges = false) {
        let attributes = super.getSaveData();
        if (this._previous !== null) {
            if (attributes.diagnosis === this._previous.diagnosis) {
                delete attributes.diagnosis;
            }
            if (attributes.complaints === this._previous.complaints) {
                delete attributes.complaints;
            }
            if (this.selectionsEqual(attributes.diagnosis_icd, this._previous.diagnosis_icd)) {
                delete attributes.diagnosis_icd;
            }
            let fields = this._previous.fields;
            attributes.fields = attributes.fields.filter((field) => {
                let prev = this.findField(fields, field.field_id);
                if (prev === undefined) {
                    return this.hasValue(field);
                }
                return this.isValueChanged(prev, field);
            }).map((field) => {
                if (_.isArray(field.option_value) && field.option_value.length === 0) {
                    field.option_value = null;
                }
                return field;
            });
        }
        if (detectNoChanges && this.detectNoChanges(attributes)) {
            return null;
        }
        return attributes;
    }

    /**
     * Get data for patch
     * 
     * @param {bool} detectNoChanges
     * 
     * @returns {object}
     */
    getPatchData(detectNoChanges = false) {
        let attributes = super.getSaveData();
        let fields = [];
        let changed = this.changed();
        delete attributes.diagnosis;
        delete attributes.complaints;
        delete attributes.diagnosis_icd;
        if (changed !== false) {
            if (changed.indexOf('diagnosis') !== -1) {
                attributes.diagnosis = this.diagnosis;
            }
            if (changed.indexOf('complaints') !== -1) {
                attributes.complaints = this.complaints;
            }
            if (changed.indexOf('diagnosis_icd') !== -1) {
                attributes.diagnosis_icd = this.diagnosis_icd;
            }
        }
        this._structure.getFields().forEach((field) => {
            if (field.touched) {
                field.bond.id = null; 
                fields.push(field.bond);
            }
        });
        attributes.fields = fields;
        if (detectNoChanges && this.detectNoChanges(attributes)) {
            return null;
        }
        return attributes;
    }

    /**
     * Check if there are card cahnges
     * 
     * @param {object} attributes 
     * 
     * @returns {bool}
     */
    detectNoChanges(attributes) {
        return attributes.diagnosis === undefined
            && attributes.complaints === undefined
            && attributes.diagnosis_icd === undefined
            && attributes.fields.length === 0;
    }

    /**
     * @inheritdoc
     */
    save(options = {}) {
        let data = this.getSaveData(true);
        if (data === null) {
            return Promise.reject(NO_CHANGES);
        }
        return super.save({
            data,
            ...options,
        });
    }

    /**
     * Save a patch
     * 
     * @param {object} options 
     */
    patch(options = {}) {
        let patch = this.getPatchData(true);
        if (patch === null) {
            return Promise.reject(NO_CHANGES);
        }
        let combinedData = {
            diagnosis: null,
            complaints: null,
            diagnosis_icd: [],
            ...this.getSaveData(),
        };
        return super.save({
            method: this.getPatchMethod(),
            data: patch,
            ...options,
        }).then(() => {
            this.mergeWith(combinedData);
            this.sync();
        });
    }

    /**
     * Merge this record with another record, giving priority to this record
     * 
     * @param {OutpatientRecord} other 
     */
    mergeWith(other) {
        if (this.diagnosis === null && other.diagnosis !== null) {
            this.diagnosis = other.diagnosis;
        }
        if (this.complaints === null && other.complaints !== null) {
            this.complaints = other.complaints;
        }
        if (this.diagnosis_icd.length === 0 && other.diagnosis_icd.length !== 0) {
            this.diagnosis_icd = other.diagnosis_icd;
        }
        if (this.diagnosis_icd_names.length === 0 && other.diagnosis_icd_names.length !== 0) {
            this.diagnosis_icd_names = other.diagnosis_icd_names;
        }

        this.mergeFields(other.fields);
    }

    /**
     * Merge this record fields with another record fields, giving priority to this record
     * 
     * @param {array} otherFields 
     */
    mergeFields(otherFields) {
        otherFields.forEach((field) => {
            if (!this.findField(this.fields, field.field_id)) {
                this.fields.push(field);
            }
        });
    }

    /**
     * Check if value was changed
     * 
     * @param {object} a 
     * @param {object} b
     * 
     * @returns {bool} 
     */
    isValueChanged(a, b) {
        return a.value !== b.value || !this.selectionsEqual(a.option_value, b.option_value);
    }

    /**
     * Check if value set
     * 
     * @param {object} a 
     * 
     * @returns {bool}
     */
    hasValue(a) {
        return _.isFilled(a.value) || this.hasSelection(a.option_value);
    }

    /**
     * Check if any options are selected
     *
     * @param {array|number} val
     *
     * @returns {bool}
     */
    hasSelection(val) {
        return _.isArray(val) ? val.length !== 0 : _.isFilled(val);
    }

    /**
     * Check if the two diagnoses are equal
     *
     * @param {array} first
     * @param {array} second
     *
     * @returns {bool}
     */
    selectionsEqual(first, second) {
        if (!_.isArray(first)) {
            first = first === null ? [] : [first];
        }
        if (!_.isArray(second)) {
            second = second === null ? [] : [second];
        }
        if (first.length !== second.length) {
            return false;
        }
        return first.every((el) => second.indexOf(el) !== -1);
    }

    /**
     * Find field by id
     *
     * @param {array} fields
     * @param {number} id
     *
     * @return {object|undefined}
     */
    findField(fields, id) {
        return _.find(fields, (f) => f.field_id == id);
    }

    /**
     * Serialize model
     *
     * @returns {object}
     */
    serialize() {
        return {
            appointment_id: this.appointment_id,
            fields: this.fields,
            diagnosis: this.diagnosis,
            diagnosis_icd: this.diagnosis_icd,
            complaints: this.complaints,
        };
    }

    /**
     * Restore model data
     *
     * @param {object} data
     */
    unserialize(data) {
        this.appointment_id = data.appointment_id;
        this.diagnosis = data.diagnosis;
        this.diagnosis_icd = [...data.diagnosis_icd];
        this.complaints = data.complaints;
        data.fields.forEach((field) => {
            let id = field.field_id;
            let f = _.find(this._structure.getFields(), (f) => f.key == id);
            if (f !== undefined) {
                if (this.isValueChanged(field, f.bond)) {
                    f.bond.value = field.value;
                    f.bond.option_value = field.option_value;
                }
            }
        });
    }

    /**
     * Get mapped structure
     *
     * @returns {Structure}
     */
    get structure() {
        return this._structure;
    }
}

export default OutpatientRecord;

export {
    NO_CHANGES,
};
