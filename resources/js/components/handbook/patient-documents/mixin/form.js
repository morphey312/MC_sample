import CreatePatientDocument from '@/components/handbook/patient-documents/FormCreate.vue';
import EditPatientDocument from '@/components/handbook/patient-documents/FormEdit.vue';

export default {
    methods: {
        getModalOptions() {
            return {
                createForm: CreatePatientDocument,
                createProps: {
                    is_official: this.filters.is_official_form,
                },
                editForm: EditPatientDocument,
                createHeader: __('Добавить документ пациента'),
                editHeader: __('Изменить документ пациента'),
                width: '700px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот документ пациента?'),
                deleted: __('Документ пациента был успешно удален'),
                created: __('Документ пациента был успешно добавлен'),
                updated: __('Документ пациента был успешно обновлен'),
            };
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
}