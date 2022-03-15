<template>
    <section>
        <manage-table
            ref="table"
            :fields="fields"
            :repository="repository"
            :selectable-rows="true"
            :enable-pagination="false"
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
            <el-button
                :disabled="activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </section>
</template>

<script>``
import FormCreate from './contractors/FormCreate.vue';
import FormEdit from './contractors/FormEdit.vue';
import SearchMsp from './contractors/SearchMsp.vue';
import ProxyRepository from '@/repositories/proxy-repository';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    props: {
        contract: Object,
        clinics: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.contract.subcontractors,
                });
            }),
            fields: [
                {
                    name: 'edrpou',
                    width: '10%',
                    title: __('ЕГРПОУ'),
                },
                {
                    name: 'name',
                    title: __('Название'),
                },
                {
                    name: 'type',
                    title: __('Тип'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$handbook.getOption('msp_type', value);
                    },
                },
                {
                    name: 'contract_number',
                    width: '15%',
                    title: __('Номер договора'),
                },
                {
                    name: 'issued_at',
                    title: __('Дата начала'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'expires_at',
                    title: __('Дата окончания'),
                    width: '15%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
            ],
        };
    },
    methods: {
        getModalOptions() {
            return {
                createForm: SearchMsp,
                editForm: FormEdit,
                editProps: () => ({
                    clinics: this.clinics,
                }),
                createHeader: __('Добавить подрядчика'),
                editHeader: __('Изменить подрядчика'),
                width: '600px',
                events: {
                    next: (dialog, le) => {
                        dialog.pushComponent(FormCreate, {
                            legalEntity: le,
                            clinics: this.clinics,
                        }, {
                            cancel: (dialog) => {
                                dialog.close();
                            },
                            created: (dialog, model) => {
                                dialog.close();
                                this.$info(__('Подрядчик был успешно добавлен'));
                                this.onCreated(model);
                                this.lastActiveItemId = model.id;
                                this.refresh();
                            },
                        })
                    }
                }
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этого подрядчика?'),
                deleted: __('Подрядчик был успешно удален'),
                updated: __('Подрядчик был успешно обновлен'),
            };
        },
        getManageTable() {
            return this.$refs.table;
        },
        onCreated(model) {
            this.contract.subcontractors.push(model);
        },
        performDelete(model) {
            let index = this.contract.subcontractors.indexOf(model);
            if (index !== -1) {
                this.contract.subcontractors.splice(index, 1);
            }
            return Promise.resolve();
        },
    }
}
</script>
