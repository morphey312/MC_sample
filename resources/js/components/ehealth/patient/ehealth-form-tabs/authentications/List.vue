<template>
    <section>
        <div v-loading="loading">
            <h3 Class="mt-0 mb-20">{{ __('Аутентификация') }}</h3>
            <manage-table
                ref="table"
                :fields="fields"
                :repository="repository"
                :selectable-rows="true"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
            </manage-table>
            <div class="mt-10">
                <el-button
                    @click="create">
                    {{ __('Добавить') }}
                </el-button>
                <el-button
                    :disabled="activeItem === null"
                    @click="edit">
                    {{ __('Редактировать') }}
                </el-button>
                <!--            <el-button-->
                <!--                :disabled="activeItem === null"-->
                <!--                @click="choose">-->
                <!--                {{ __('Выбрать') }}-->
                <!--            </el-button>-->
                <el-button
                    :disabled="canDeactive()"
                    @click="remove">
                    {{ __('Деактивировать') }}
                </el-button>
            </div>
        </div>
    </section>
</template>

<script>
import FormCreate from './FormCreate.vue';
import FormEdit from './FormEdit.vue';
import ProxyRepository from '@/repositories/proxy-repository';
import ManageMixin from '@/mixins/manage';
import BatchRequest from '@/services/batch-request';
import ehealth from '@/services/ehealth';
import CONSTANTS from '@/constants';
import CONSTANT from "@/constants";
import FormDelete from "@/components/ehealth/patient/ehealth-form-tabs/authentications/FormDelete.vue";

export default {
    mixins: [
        ManageMixin,
    ],
    props: {
        patient: Object,
    },
    mounted() {
        if (this.patientSigned) {
            this.loadAuthenticationMethods();
        } else {
            this.loading = false;
        }
    },
    computed: {
        patientSigned() {
            return this.patient.ehealth_status === CONSTANTS.EHEALTH_PATIENT.STATUS.SIGNED
        },
        thirdPrsonMethod() {
            return CONSTANT.EHEALTH_PATIENT.AUTHENTICATION_TYPE.THIRD_PERSON;
        },
    },
    data() {
        return {
            loading: true,
            batchRequest: new BatchRequest('/api/v1/ehealth/patient/authentications/batch'),
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.patient.patient_authentications,
                });
            }),
            fields: [
                {
                    name: 'type',
                    title: __('Тип аутентификации'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$handbook.getOption('ehealth_authentication_method', value);
                    },
                },
                {
                    name: 'phone_number',
                    width: '20%',
                    title: __('Номер телефона'),
                },
                {
                    name: 'alias',
                    title: __('Псевдоним'),
                    width: '20%',
                },
                {
                    name: 'ended_at',
                    title: __('Ативный до'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
            ],
        };
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
                },
                createCustomClass: "padding-0",
                editCustomClass: "padding-0",
                createHeader: __('Добавить метод аунтификации'),
                editHeader: __('Редактировать метод аутентификации'),
                width: '600px',
                events: {
                    done: (dialog, model) => {
                        dialog.close();
                        this.updatePatientAuthMethods(model)
                    },
                },
            };
        },
        getManageTable() {
            return this.$refs.table;
        },
        onCreated(model) {
            if (this.patientSigned) {
                this.loading = true;
                this.loadAuthenticationMethods()
            } else {
                this.patient.patient_authentications.push(model);
            }
        },
        loadAuthenticationMethods() {
            ehealth.getPatientAuthenticationMethods(this.patient.ehealth_id).then((response) => {
                if (response.data) {
                    this.patient.patient_authentications = response.data.map(authentication => {
                        authentication.type = authentication.type.toLowerCase();
                        return authentication
                    });
                    this.$refs.table.refresh();
                }
                this.$nextTick(() => {
                    this.loading = false;
                });
            });
        },
        remove() {
            if (this.patient.patient_authentications.length > 1) {
                if (this.patient.isNew()) {
                    this.$confirm(__('Вы уверены, что хотите удалить эту запись?'), () => {
                        this.performDelete(this.activeItem).then(() => {
                            this.$info(__('Запись была успешно удалена'));
                            this.lastActiveItemId = null;
                            this.activeItem = null;
                            this.refresh();
                        });
                    });
                } else {
                    this.$modalComponent(FormDelete, {
                        item: this.activeItem,
                        patient: this.patient,
                    }, {
                        close: (dialog) => {
                            dialog.close();
                        },
                        done: (dialog, model) => {
                            dialog.close();
                            this.$info(__('Выбранный метод аунтификации успешно деактивирован'))
                            this.updatePatientAuthMethods(model)
                        },
                    }, {
                        header: __('Деактивировать метод аутентификации'),
                        width: '600px',
                        customClass: 'padding-0',
                    });
                }
            } else {
                this.$error(__('Последний метод аутентификации деактивировать невозможно'));
            }
        },
        canDeactive() {
            if (this.activeItem) {
                if (this.patient.isNew()) {
                    return false
                } else if (!this.patient.isNew() && this.activeItem.type === this.thirdPrsonMethod) {
                    return false
                }
            }
            return true
        },
        canEdit() {
            if (this.activeItem) {
                if (this.patient.ehealth_status === CONSTANTS.EHEALTH_PATIENT.STATUS.SIGNED) {
                    return false
                }
            }
            return true
        },
        updatePatientAuthMethods(model) {
            if (this.patientSigned) {
                this.loading = true;
                this.loadAuthenticationMethods()
            } else {
                this.patient.patient_authentications.push(model);
            }
        }
    }
}
</script>
