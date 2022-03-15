import PatientRepository from '@/repositories/patient';
import SearchResult from '@/components/patients/duplicates/SearchResult.vue';


export default {

    methods: {

        simpleSave () {
            this.model.save().then((response) => {
                this.$emit('created', this.model);
            }).catch((e) => {
                this.onSaveError(e);
                this.$displayErrors(e);
            });
        },

        create() {
            if (typeof this.model.source_id === 'object' && this.model.source_id) {
                this.model.source_id = this.model.source_id.id || this.model.source_id
            }

            this.$clearErrors();
            let model = this.model;

            let contact = model.contact ? model.contact : model;
            let caller = model.contact ? 'call' : null;

            let fio = {};
            if(contact.lastname){
                fio.lastname = `=${contact.lastname}`;
            }
            if(contact.firstname){
                fio.firstname = `=${contact.firstname}`;
            }
            if(contact.middlename){
                fio.middlename = `=${contact.middlename}`;
            }
            let phonearray = [];
            phonearray.push(contact.contact_details.primary_phone_number);
            if(contact.contact_details.secondary_phone_number){
                phonearray.push(contact.contact_details.secondary_phone_number);
            }

            let filter = {
                or: {
                    and: fio,
                    phonearray
                }
            };

            let repository = new PatientRepository();


            if(contact.isNew()){
                model.validate().then((e) => {
                    if (e && Object.keys(e).length !== 0) {
                        this.$displayErrors({errors: e});
                    } else {
                        let patients = repository.fetch(filter).then((data)=>{
                            if(data.rows.length) {
                                this.$modalComponent(SearchResult, {
                                    items: data,
                                    caller: caller,
                                    model: contact
                                },
                                {
                                    cancel: (dialog) => {
                                        dialog.close();
                                    },
                                    closeandsave: (dialog) => {
                                        this.model.save().then((response) => {

                                            this.$emit("cancel");
                                            this.$emit('created', model);

                                            dialog.close();
                                        }).catch((e) => {
                                            this.onSaveError(e);
                                            this.$displayErrors(e);
                                            dialog.close();
                                        });

                                    },
                                    selected: (dialog, list) => {
                                        dialog.close();
                                    },
                                    deleted: (dialog, index) => {
                                    },
                                }, {
                                    header: __('Такие пациенты уже есть в базе'),
                                    width: '1200px',
                                    customClass: 'padding-0',
                                });

                            }else{
                                this.simpleSave();
                            }

                        });
                    }
                });
            }else{
                this.simpleSave();
            }
        }
    },
}


