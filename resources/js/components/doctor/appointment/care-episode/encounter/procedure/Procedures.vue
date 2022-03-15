<template>
    <div class="conditions-container">
        <procedures-list
            ref="table"
            :procedures="procedures"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <el-button
                @click="chechServices">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="cancel">
                {{ __('Удалить') }}
            </el-button>
        </div>
        <slot name="buttons"></slot>
    </div>
</template>

<script>
import ManageMixin from '@/mixins/manage';
import ProceduresList from './List.vue';
import CancelForm from './CancelForm.vue';
import CreateProcedure from './CreateForm';
import EditProcedure from './EditForm';

export default {
    components: {
        ProceduresList,
    },
    props: {
        encounter: Object,
        appointment: Object,
    },
    mixins: [
        ManageMixin,
    ],
    watch: {
        procedures() {
            this.$nextTick(() => {
                this.refresh();
            });
        },
        ['encounter.id']() {
            this.getProcedures()
        }
    },
    beforeMount() {
        this.getProcedures();
    },
    data() {
        return {
            loading: true,
            procedures: {},
        }
    },
    computed: {
        getEhealthServices() {
            let services = this.appointment.services.filter(service => !_.isNull(service.ehealth_service_id)).map((service) => ({
                id: service.service_id,
                value: service.name,
            }));

            return services;
        },
    },
    methods: {
        getProcedures() {
            let model = this.encounter;
            model.fetch(['procedures']).then(() => {
                this.procedures = model.procedures;
                this.loading = false;
            });
        },
        chechServices() {
            if (this.getEhealthServices.length === 0) {
                this.$error(__('В записи нет подходящих услуг'))
            } else {
                this.create();
            }
        },
        getModalOptions() {
            return {
                createForm: CreateProcedure,
                createProps: {
                    encounter: this.encounter,
                    services: this.getEhealthServices,
                    clinic_id: this.appointment.clinic_id,
                    doctor_id: this.appointment.doctor_id,
                },
                editForm: EditProcedure,
                editProps: {
                    item: this.activeItem,
                    services: this.getEhealthServices,
                },
                createHeader: __('Добавить процедуру пациента'),
                editHeader: __('Изменить процедуру пациента'),
                width: '800px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить запись?'),
                deleted: __('Запись успешно удалена'),
                created: __('Запись успешно добавлена'),
                updated: __('Запись успешно обновлена'),
            };
        },
        cancel() {
            this.$modalComponent(CancelForm, {
                model: this.activeItem,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog) => {
                    dialog.close();
                    this.procedures = this.procedures.filter((e) => e.id !== data.id);
                },
            }, {
                header: __('Удаление процедуры'),
                width: '500px',
            });
        },
        onCreated(procedure) {
            this.procedures = [
                ...this.procedures,
                {
                    id: procedure.id,
                    data: procedure,
                },
            ];
        },
        onUpdated(procedure) {
            this.procedures = this.procedures.map((e) => {
                if (e.data.id === model.id) {
                    return {
                        id: procedure.id,
                        data: procedure,
                    };
                }

                return e;
            });
        },
    },
}
</script>
