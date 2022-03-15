<template>
    <div
        v-loading="loading"
        class="separate-form analysis-modal-form"
    >
        <el-tabs
            v-model="activeTab"
            class="tab-group-grey"
        >
            <el-tab-pane
                :lazy="true"
                :label="__('Медикаменты клиники')"
                name="ordinary"
            >
                <section class="grey pt-0 pb-0">
                    <search-table
                        ref="table"
                        :featured-list="featuredList"
                        :is-doctor="isDoctor"
                        :clinic="clinic"
                        @loaded="refreshed"
                        @selection-changed="addToSelected"
                        @featured-changed="toggleFeatured"
                    />
                </section>
                <section>
                    <template v-if="emptySelected && prevAssignedList.length == 0">
                        <b>{{ __('Выбранные медикаменты') }}</b>
                        <empty-section
                            :show-image="false"
                            list-class="text-only"
                        >
                            <b>{{ __('Чтобы назначить медикаменты, сначала выберите их из таблицы выше') }}</b><br>
                            {{ __('Нажмите "выбрать медикамент" в крайней правой колонке.') }}
                        </empty-section>
                    </template>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Избранные медикаменты')"
                name="featured"
            >
                <section class="pt-0">
                    <featured-table
                        :featured-list="featuredList"
                        :clinic="clinic"
                        @selection-changed="addToSelected"
                        @featured-changed="toggleFeatured"
                    />
                    <template v-if="emptySelected">
                        <div class="pt-20">
                            <b>{{ __('Выбранные медикаменты') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only"
                            >
                                <b>{{ __('Чтобы назначить медикаменты, сначала выберите их из таблицы выше') }}</b><br>
                                {{ __('Нажмите "выбрать медикамент" в крайней правой колонке.') }}
                            </empty-section>
                        </div>
                    </template>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Избранные из аптек')"
                name="outclinic"
            >
                <section class="pt-0">
                    <outclinic-table
                        :medicines="doctorOutclinicList"
                        @selection-changed="addOutclinicToSelected"
                        @medicine-deleted="getDoctorOutclinicMedicines"
                    />
                    <template v-if="emptySelected">
                        <div class="pt-20">
                            <b>{{ __('Выбранные медикаменты') }}</b>
                            <empty-section
                                :show-image="false"
                                list-class="text-only"
                            >
                                <b>{{ __('Чтобы назначить медикаменты, сначала выберите их из таблицы выше') }}</b><br>
                                {{ __('Нажмите "выбрать медикамент" в крайней правой колонке.') }}
                            </empty-section>
                        </div>
                    </template>
                </section>
            </el-tab-pane>
        </el-tabs>
        <section
            v-if="!emptySelected || prevAssignedList.length > 0"
            class="pt-0"
        >
            <b>{{ __('Выбранные медикаменты') }}</b>
            <selected-table
                :rows="selectedRows"
                :prev-assigned-list="prevAssignedList"
                :readonly="readonly"
                :is-doctor="isDoctor"
                :paid-medicine="costType"
                :insurance-policy="insurancePolicy"
                @selection-changed="removeFromSelected"
                @delete-prev="deletePrev"
                @cost-changed="calcModelPrice"
            />
        </section>
        <div class="dialog-footer text-right">
            <p class="">
                <b>{{ __('Итого стоимость медикаментов клиники:') }} {{ totalCost }}</b><br>
            </p>
            <el-button
                @click="cancel"
            >
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="emptySelected && prevAssignedList.length == 0"
                @click="performConfirm"
            >
                {{ __('Выбрать и сохранить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import SearchTable from './SearchTable.vue';
import FeaturedTable from './FeaturedTable.vue';
import OutclinicTable from './OutclinicTable.vue';
import SelectedTable from './SelectedTable.vue';
import EmptySection from '@/components/appointments/modal/form/treatments/analysis/Empty.vue';
import Employee from '@/models/employee';
import AssignedMedicine from '@/models/patient/assigned-medicine';
import ManageMixin from '@/mixins/manage';
import SearchMixin from '@/components/doctor/appointment/mixins/search';
import OutClinicMedicineForm from './out-clinic/MedicineForm.vue';
import AssignedMedicineRepository from '@/repositories/patient/assigned-medicine';
import BatchRequest from '@/services/batch-request';
import CONSTANTS from '@/constants';

export default {
    components: {
        SearchTable,
        FeaturedTable,
        SelectedTable,
        EmptySection,
        OutclinicTable,
    },
    mixins: [
        ManageMixin,
        SearchMixin,
    ],
    props: {
        doctor: Object,
        medicines: Array,
        clinic: Number,
        patient: Object,
        readonly: Boolean,
        clinicWorksWithApteka24: Boolean,
        insurancePolicy: Object,
        costInitial: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            activeTab: 'ordinary',
            selectedRows: [...this.medicines],
            employee: new Employee({id: this.doctor.id}),
            loading: true,
            costType: this.costInitial,
            featuredList: [],
            doctorOutclinicList: [],
            prevAssignedList: [],
            batchRequest: new BatchRequest('/api/v1/patients/assigned-medicines/batch'),
            isDoctor: this.$store.state.user.isDoctor,
        };
    },
    mounted() {
        this.getDoctorMedicines();
    },
    methods: {
        getPrevAssignedMedicines() {
            let filters =  {
                patient: [this.patient.id],
                assigner: [this.doctor.id],
                clinic: this.clinics,
                should_issue: true,
                medicine_type: CONSTANTS.ASSIGNED_MEDICINE.TYPES.MEDICINE,
                skip_id: this.medicines.map(medicine => medicine.id),
            };

            let repo = new AssignedMedicineRepository();
            return repo.fetch(filters, null, ['medicine', 'last_issue']).then((response) => {
                this.prevAssignedList = response.rows.map(item => {
                    item.prev_assigned = true;
                    return item;
                });
                return Promise.resolve();
            });
        },
        deletePrev(medicine) {
            let index = this.prevAssignedList.findIndex(row => row.id == medicine.id);
            if (index !== -1) {
                medicine.delete().then(() => {
                    this.prevAssignedList.splice(index, 1);
                });
            }
        },
        performConfirm() {
            this.batchRequest.reset();
            this.loading = true;

            this.prevAssignedList.forEach(row => {
                this.batchRequest.update(row);
            });


            this.batchRequest.submit().then((result) => {
                if (result.failure.length !== 0) {
                    this.$error(__('Не удалось сохранить некоторые данные'));
                    this.getPrevAssignedMedicines();
                    return;
                }
                this.loading = false;
                this.confirm();
            }).catch((error) => {
                this.loading = false;
                if (error.invalid) {
                    this.$error(__('Пожалуйста, проверьте правильность введенных данных'));
                }
            });
        },
        getFilterUid() {
            return false;
        },
        getMedicines() {
            return this.medicines.map(medicine => {
                if (medicine instanceof AssignedMedicine) {
                    return new AssignedMedicine(medicine._attributes);
                } else {
                    return new AssignedMedicine(medicine);
                }
            });
        },
        toggleCost(val) {
            this.costType = val;
        },
        addOutClinicMedicine() {
            this.$modalComponent(OutClinicMedicineForm, {
                featuredOutclinicList: this.doctorOutclinicList,
                clinic: this.clinic,
                clinicWorksWithApteka24: this.clinicWorksWithApteka24,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, list) => {
                    dialog.close();
                    this.saveOutclinicMedicines(list);
                },
            }, {
                header: __('Добавить медикаменты из аптек'),
                width: '355px',
                customClass: 'padding-0',
            });
        },
        saveOutclinicMedicines(list) {
            let medicines = list.filter(item => _.isVoid(item.id))
                .map(item => ({
                    name: item.name,
                    is_apteka24: item.is_apteka24,
                    apteka24_id: item.apteka24_id
                }));

            this.employee.saveOutclinicMedicines(medicines).then((response) => {
                if (response && response.length != 0) {
                    list = list.map((item) => {
                        if (_.isFilled(item.id)) {
                            return item;
                        }
                        let match = response.find(row => item.name == row.name);
                        if (match) {
                            item.id = match.id;
                        }
                        return item;
                    });
                    this.addOutclinicList(list);
                } else {
                    if (list.length != 0) {
                        this.addOutclinicList(list);
                    }
                }
            });
        },
        addOutclinicList(list) {
            list.forEach((item) => {
                if (_.isFilled(item.id)) {
                    this.addOutclinicToSelected(item);
                }
            });
        },
        getOutclinicItem(item) {
            return new AssignedMedicine({
                clinic_id: this.clinic,
                medicine_id: item.id,
                medicine_type: CONSTANTS.ASSIGNED_MEDICINE.TYPES.OUTCLINIC_MEDICINE,
                name: item.name,
                quantity: item.quantity,
                medication_duration: item.medication_duration,
                is_apteka24: item.is_apteka24,
                apteka24_id: item.apteka24_id,
                comment: item.comment,
            });
        },
        getDoctorMedicines() {
            let filters = {clinic: this.clinic};
            this.employee.fetchFeaturedMedicines(filters).then((response) => {
                let medicine = new AssignedMedicine();
                this.featuredList = response.map(row => medicine.createAssignedMedicine(row, filters));
                return this.getDoctorOutclinicMedicines();
            });
        },
        getDoctorOutclinicMedicines() {
            this.employee.fetchOutclinicMedicines().then((response) => {
                this.doctorOutclinicList = response;
                return this.getPrevAssignedMedicines().then(() => {
                    this.loading = false;
                });
            });
        },
        toggleDoctorFeatured(medicine_id) {
            this.employee.saveFeaturedMedicine(medicine_id);
        },
        toggleFeatured(medicine) {
            let featuredIndex = this.featuredList.findIndex((item) => {
                return item.medicine_id === medicine.medicine_id;
            });

            if (featuredIndex === -1) {
                this.featuredList.push(medicine);
            } else {
                this.featuredList.splice(featuredIndex, 1);
            }

            this.toggleDoctorFeatured(medicine.medicine_id);
        },
        addOutclinicToSelected(row) {
            let sameIndex = this.findSelectedRowIndex(row, CONSTANTS.ASSIGNED_MEDICINE.TYPES.OUTCLINIC_MEDICINE, 'id');
            if (sameIndex != undefined && sameIndex !== -1) {
                return this.warnMedicineSelected(row, sameIndex);
            }
            let medicine = this.getOutclinicItem(row);
            this.selectedRows.push(medicine);
        },
        addToSelected({row, index}) {
            let sameIndex = this.findSelectedRowIndex(row, CONSTANTS.ASSIGNED_MEDICINE.TYPES.MEDICINE);
            if (sameIndex != undefined && sameIndex !== -1) {
                return this.warnMedicineSelected(row, sameIndex);
            }
            let medicine = this.getMedicineItem(row);
            medicine.is_free = !this.costType;
            this.selectedRows.push(medicine);
        },
        warnMedicineSelected(row, index) {
            let warning = __('{name} уже присутствует в выбранном списке. Увеличить количество?', {name: row.name});
            return this.$confirm(warning, () => {
                let medicine = this.selectedRows[index];
                medicine.quantity++;
                this.calcModelPrice(medicine);
            });
        },
        findSelectedRowIndex(row, medicine_type, field = 'medicine_id') {
            return this.selectedRows.findIndex((item) => {
                return item.medicine_id == row[field] &&
                       item.medicine_type == medicine_type &&
                       item.is_free != this.costType;
            });
        },
        getMedicineItem(data) {
            return new AssignedMedicine({
                clinic_id: this.clinic,
                medicine_id: data.medicine_id,
                medicine_type: CONSTANTS.ASSIGNED_MEDICINE.TYPES.MEDICINE,
                name: data.name,
                cost: (this.costType === false ? 0 : Math.round(data.cost)),
                self_cost: data.self_cost,
                base_cost: data.base_cost,
                min_cost: data.base_cost,
                is_apteka24: data.is_apteka24,
                apteka24_id: data.apteka24_id,
                is_free: this.costType ? false : true,
            });
        },
        removeFromSelected({row, index}) {
            let model = this.selectedRows[index];

            if (_.isFilled(model)) {
                if (model.isNew()) {
                    this.selectedRows.splice(index, 1);
                } else {
                    model.delete().then((response) => {
                        this.selectedRows.splice(index, 1);
                        this.$emit('deleted', index);
                        return this.$info(__('Медикамент успешно удален'));
                    }).catch((error) => {
                        return this.$error(__('Не удалось удалить назначеный медикамент'));
                    });
                }
            }
        },
        calcModelPrice(model) {
            if (model.medicine_type == CONSTANTS.ASSIGNED_MEDICINE.TYPES.OUTCLINIC_MEDICINE) {
                return;
            }

            //Set cost for prev assignment
            if (model.prev_assigned) {
                model.cost = this.getModelCost(model);
                return;
            }

            //Set cost for current assignment
            if (model.is_free) {
                model.cost = 0;
            } else {
                model.cost = this.getModelCost(model);
            }
        },
        getModelCost(model) {
            return _.isFilled(model.base_cost)
                ? Math.round(Number(model.base_cost) * Number(model.quantity))
                : 0;
        }
    }
}
</script>
