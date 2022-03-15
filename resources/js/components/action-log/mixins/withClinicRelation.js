import DefaultLog from "../Default";

export default {
    methods: {
        getChanges(row) {
            let clinic = row.new.new_clinic;
            let oldClinic = row.old.old_clinic;
            delete row.new.new_clinic;
            delete row.old.old_clinic;

            let result = DefaultLog.methods.getChanges.call(this, row);

            if (clinic !== undefined) {
                result = result.map((item) => {
                    item.label = `${item.label}, ${clinic}`;
                    return item;
                });
            }

            if (clinic !== oldClinic) {
                result.unshift({
                    label: this.fieldLabel('clinic'),
                    old: this.formatFieldValue('clinic', oldClinic),
                    new: this.formatFieldValue('clinic', clinic),
                });
            }

            return result;
        },
    }
}
