<template>
    <div class="separate-form analysis-modal-form" v-loading="saving">
        <section class="grey pt-0 pb-0">
            <search-table
                ref="table"
                :clinic="clinic"
                @loaded="refreshed"
                @selection-changed="addToSelected"
            />
        </section>
        <section>
            <template v-if="emptySelected">
                <b>{{ __('Выбранные медикаменты') }}</b>
                <empty-section 
                    :show-image="false"
                    list-class="text-only">
                    <b>{{ __('Чтобы назначить медикаменты, сначала выберите их из таблицы выше') }}</b><br>
                    {{ __('Нажмите "выбрать медикамент" в крайней правой колонке.') }}
                </empty-section>
            </template>
        </section>
        <section v-if="!emptySelected" class="pt-0">
            <b>{{ __('Выбранные медикаменты') }}</b>
            <selected-table
                :rows="selectedRows"
                :patient-cards="patientCards"
                :doctors="doctors"
                @selection-changed="removeFromSelected"
                @cost-changed="calcModelPrice" />
        </section>
        <div class="dialog-footer text-right">
            <p class="inline-block input-label">{{ __('Итоговая сумма назначенных медикаментов клиники:') }} {{ totalCost }}</p>
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="confirm"
                :disabled="emptySelected">
                {{ __('Выбрать') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import SearchTable from './SearchTable.vue';
import SelectedTable from './SelectedTable.vue';
import EmptySection from '@/components/appointments/modal/form/treatments/analysis/Empty.vue';
import AssignedMedicine from '@/models/patient/assigned-medicine';
import EmployeeRepository from '@/repositories/employee';
import ManageMixin from '@/mixins/manage';
import SearchMixin from '@/components/doctor/appointment/mixins/search';
import BatchRequest from '@/services/batch-request';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ManageMixin,
        SearchMixin,
    ],
    components: {
        SearchTable,
        SelectedTable,
        EmptySection,
    },
    props: {
        patient: Object,
        employee: Object,
        isFree: Boolean,
        clinic: Array,
    },
    data() {
        return {
            activeTab: 'ordinary',
            selectedRows: [],
            saving: false,
            batchRequest: new BatchRequest('/api/v1/patients/assigned-medicines/batch'),
            patientCards: this.getPatientCards(),
            doctors: [],
        };
    },
    mounted() {
        this.getDoctors();
    },
    methods: {
        getFilterUid() {
            return false;
        },
        addToSelected({row, index}) {
            let sameIndex = this.findSelectedRowIndex(row, CONSTANTS.ASSIGNED_MEDICINE.TYPES.MEDICINE);
            if (sameIndex != undefined && sameIndex !== -1) {
                return this.warnMedicineSelected(row, sameIndex);
            }
            let medicine = this.getMedicineItem(row);
            this.selectedRows.splice(0, 0, medicine);
        },
        findSelectedRowIndex(row, medicine_type, field = 'medicine_id') {
            return this.selectedRows.findIndex((item) => {
                return item.medicine_id == row[field] && item.medicine_type == medicine_type;
            });
        },
        warnMedicineSelected(row, index) {
            let warning = __('{name} уже присутствует в выбранном списке. Увеличить количество?', {name: row.name});
            return this.$confirm(warning, () => {
                let medicine = this.selectedRows[index];
                medicine.quantity++;
                this.calcModelPrice(medicine);
            });
        },
        getMedicineItem(data) {
            return new AssignedMedicine({
                assigner_id: this.$store.state.user.isDoctor ? this.employee.id : null,
                clinic_id: this.clinic[0],
                patient_id: this.patient.id,
                medicine_id: data.medicine_id,
                medicine_type: CONSTANTS.ASSIGNED_MEDICINE.TYPES.MEDICINE,
                name: data.name,
                cost: (this.isFree === true ? 0 : data.cost),
                self_cost: data.self_cost,
                base_cost: data.base_cost,
                is_free: this.isFree,
            });
        },
        removeFromSelected(index) {
            this.selectedRows.splice(index, 1);
        },
        calcModelPrice(model) {
            model.is_free = this.isFree;
            if (this.isFree === true) {
                model.cost = 0;
            } else {
                model.cost = Math.round(Number(model.base_cost) * Number(model.quantity)).toFixed(2);
            }
        },
        hasIncorrectItems() {
            let incorrectItem = this.selectedRows.find(item => {
                return _.isVoid(item.card_specialization_id) || _.isVoid(item.assigner_id);
            });
            return incorrectItem !== undefined;
        },
        confirm() {
            if (this.isFree) {
                if (this.hasIncorrectItems()) {
                    return this.$error(__('Выберите карту и врача'));
                }
            }
            return this.submitBatch();
        },
        submitBatch() {
            this.batchRequest.reset();
            this.selectedRows.forEach((row) => {
                if (!this.isFree && _.isVoid(row.assigner_id)) {
                    row.assigner_id = this.employee.id;
                }
                this.batchRequest.create(row);
            });
            this.saving = true;
            this.invalid = [];
            this.batchRequest.submit().then((result) => {
                this.saving = false;
                if (result.failure.length !== 0) {
                    this.$error(__('Не удалось сохранить некоторые данные'));
                    this.invalid = result.failure;
                } else {
                    this.$info(__('Данные успешно обновлены'));
                    this.$emit('selected');
                }
            }).catch((error) => {
                this.saving = false;
                if (error.invalid) {
                    this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
                    this.invalid = error.invalid;
                }
            });
        },
        getPatientCards() {
            return this.patient.getCardsWithSpecializations().map(card => {
                return {
                    id: card.specialization_id,
                    value: card.number,
                };
            });
        },
        getDoctors() {
            let employee = new EmployeeRepository();
            let filter = {
                employee_clinic: {
                    clinic: this.clinic,
                    specialization: this.patient.cardsSpecializations,
                    status_not: CONSTANTS.EMPLOYEE.STATUSES.NOT_WORKING,
                },
            };
            employee.fetchList(filter).then(response => {
                this.doctors = response;
            });
        },
    }
}
</script>