<template>
    <div class="conditions-container">
        <diagnostics-list
            ref="table"
            :diagnostics="diagnostics"
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
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
        <slot name="buttons"></slot>
    </div>
</template>

<script>
import ManageMixin from '@/mixins/manage';
import DiagnosticsList from './List.vue';
import CreateDiagnostic from './CreateForm';
import EditDiagnostic from './EditForm';

export default {
    components: {
        DiagnosticsList,
    },
    props: {
        encounter: Object,
        appointment: Object,
    },
    mixins: [
        ManageMixin,
    ],
    watch: {
        diagnostics() {
            this.$nextTick(() => {
                this.refresh();
            });
        },
        ['encounter.id']() {
            this.loading = true;
            this.getDiagnostics();
        }
    },
    beforeMount() {
        this.getDiagnostics();
    },
    data() {
        return {
            loading: true,
            diagnostics: {},
        }
    },
    computed: {
        getEhealthServices() {
            let services = this.appointment.services.filter(service => !_.isNull(service.ehealth_service_id)).map((service) => ({
                id: service.service_id,
                value: service.name,
            }));

            return services;
        }
    },
    methods: {
        getDiagnostics() {
            let model = this.encounter;
            model.fetch(['diagnosticProcedures']).then(() => {
                this.diagnostics = model.diagnostics;
                this.loading = false;
            });
        },
        chechServices() {
            if (this.getEhealthServices.length === 0) {
                this.$error(__('В записи нет подходящих услуг'));
            } else {
                this.create();
            }
        },
        getModalOptions() {
            return {
                createForm: CreateDiagnostic,
                createProps: {
                    encounter: this.encounter,
                    services: this.getEhealthServices,
                    clinic_id: this.appointment.clinic_id,
                    doctor_id: this.appointment.doctor_id,
                },
                editForm: EditDiagnostic,
                editProps: {
                    item: this.activeItem,
                    services: this.getEhealthServices,
                },
                createHeader: __('Добавить диагностику пациента'),
                editHeader: __('Изменить диагностику пациента'),
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
        onCreated(diagnostic) {
            this.diagnostics = [
                ...this.diagnostics,
                {
                    id: diagnostic.id,
                    data: diagnostic,
                },
            ];
        },
        onUpdated(diagnostic) {
            this.diagnostics = this.diagnostics.map((e) => {
                if (e.data.id === model.id) {
                    return {
                        id: diagnostic.id,
                        data: diagnostic,
                    };
                }

                return e;
            });
        },
        onDeleted(diagnostic) {
            this.diagnostics = this.diagnostics.filter((e) => e.id !== diagnostic.id);
        },
    },
}
</script>
