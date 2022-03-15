<template>
    <section>
        <h3 Class="mt-0 mb-20">{{ __('Документы') }}</h3>
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
            <el-button
                :disabled="activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </section>
</template>

<script>
import FormCreate from './FormCreate.vue';
import FormEdit from './FormEdit.vue';
import ProxyRepository from '@/repositories/proxy-repository';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    props: {
        employee: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.employee.employee_documents,
                });
            }),
            fields: [
                {
                    name: 'type',
                    title: __('Тип'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$handbook.getOption('person_document', value);
                    },
                },
                {
                    name: 'number',
                    width: '20%',
                    title: __('Номер'),
                },
                {
                    name: 'issued_at',
                    title: __('Дата выдачи'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'issued_by',
                    width: '30%',
                    title: __('Кем выдан'),
                },
            ],
        };
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    employee: this.employee,
                },
                editForm: FormEdit,
                createHeader: __('Добавить документ'),
                editHeader: __('Изменить документ'),
                width: '600px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот документ?'),
                deleted: __('Документ был успешно удален'),
                created: __('Документ был успешно добавлен'),
                updated: __('Документ был успешно обновлен'),
            };
        },
        getManageTable() {
            return this.$refs.table;
        },
        onCreated(model) {
            this.employee.employee_documents.push(model);
        },
        performDelete(model) {
            let index = this.employee.employee_documents.indexOf(model);
            if (index !== -1) {
                this.employee.employee_documents.splice(index, 1);
            }
            return Promise.resolve();
        },
    }
}
</script>