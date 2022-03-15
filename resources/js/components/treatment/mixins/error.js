export default {
    methods: {
        manageErrors(errors) {
            let errorsList = [];

            for(name in errors) {
                let error = name.split('.');
                if(error.length > 1 && this.model._attributes[error[0]]) {
                    let textParts = errors[name][0].split('|');
                    let title = this.findInDataList(textParts[0]);
                    errorsList.push(`${title} - ${textParts[1]}`);
                }
            }

            if(_.keys(this.errorFields).length > 0){
                for(name in errors) {
                    if(this.errorFields[name]) {
                        errorsList.push(`${this.errorFields[name]} - ${errors[name]}`);
                    }
                }
            }

            return errorsList;
        },
        findInDataList(key) {
            return _.find(this.clinic_list, {key: +key}).label;
        },
    }
}