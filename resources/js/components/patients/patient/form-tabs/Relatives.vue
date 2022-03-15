<template>
    <section>
        <relative-list 
            v-if="patient !== null"
            ref="table"
            :patient="patient"
            @loaded="refreshed"
            @selection-changed="setActiveItem"
        />
        <div class="mt-10">
            <el-button
                v-if="$can('patient-relations.create')"
                :disabled="patient === null || patient.loading"
                @click="create">
                {{ __('Добавить отношение') }}
            </el-button>
            <el-button
                v-if="$can('patient-relations.update')"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="edit">
                {{ __('Редактировать отношение') }}
            </el-button>
            <el-button
                v-if="$can('patient-relations.delete')"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="remove">
                {{ __('Удалить отношение') }}
            </el-button>
            <el-button
                v-if="$can('patients.update')"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="editRelative">
                {{ __('Изменить данные родственника') }}
            </el-button>
            <el-button
                :disabled="!isRouteSchedule || (isRouteSchedule && activeItem === null)"
                @click="setSchedulePatient">
                {{ __('Задать для записи на прием') }}
            </el-button>
        </div>
    </section>
</template>
<script>
import RelativeList from '@/components/patients/relatives/List.vue';
import FormCreate from '@/components/patients/relatives/FormCreate.vue';
import FormEdit from '@/components/patients/relatives/FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import Patient from '@/models/patient';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        RelativeList,
    },
    props: {
        patient: Object,
    },
    computed: {
        isRouteSchedule() {
            return this.$router.currentRoute.name === 'appointment-schedule';
        },
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    patient: this.patient,
                },
                editForm: FormEdit,
                editProps: {
                    patient: this.patient,
                    item: this.activeItem,
                },
                createHeader: __('Добавить родственное отношение'),
                editHeader: __('Изменить родственное отношение'),
                width: '710px',
            };
        },
        onCreated(relative) {
            this.$emit('add-relative', relative);
        },
        onUpdated(relative) {
            this.$emit('update-relative', relative);
        },
        remove() {
            this.$confirm(__('Вы уверены, что хотите удалить это родственное отношение?'), () => {
                return this.onDelete();
            });
        },
        onDelete() {
            this.$emit('delete-relative', this.activeItem);
            this.setActiveItem([]);
            this.refresh();
        },
        getMessages() {
            return {
                created: __('Родственное отношение было успешно добавлено'),
                updated: __('Родственное отношение было успешно обновлено'),
            };
        },
        editRelative() {
            this.displayEditPatientForm(this.activeItem.id, 
                (patient) => {
                    this.activeItem.firstname = patient.firstname;
                    this.activeItem.lastname = patient.lastname;
                    this.activeItem.middlename = patient.middlename;
                    this.activeItem.full_name = patient.full_name;
                    this.activeItem.birthday = patient.birthday;
                },
            );
        },
        setSchedulePatient() {
            let patient = new Patient({id: this.activeItem.id});
            patient.fetch().then(() => {
                this.$eventHub.$emit('set-schedule-patient', {patient});
                this.$info(__('Пациент успешно задан'));
            });
        },
    },
}
</script>