<template>
    <form-row name="employees" v-loading="loading">
        <div class="content-wrapper">
            <section class="grey">
                <manage-table
                    ref="table"
                    :fields="fields"
                    :repository="repository"
                    table-height="auto"
                    :enable-pagination="false">
                    <template slot="action" slot-scope="props">
                        <div class="has-icon">
                            <a href="#" @click.prevent="removeEmployee(props.rowData.id)">{{ __('Удалить пользователя') }}</a>
                        </div>
                    </template>
                </manage-table>
            </section>
        </div>
        <el-row :gutter="20">
            <el-col :span="16">
                <form-select
                    :entity="selectedEmployee"
                    :repository="employees"
                    :clearable="true"
                    property="id"
                    :label="__('Сотрудник')" />
            </el-col>
             <el-col :span="8" class="mt-20">
                 <el-button @click="addEmployee()">{{ __('Добавить') }}</el-button>
             </el-col>
        </el-row>
        <div
            class="dialog-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="save">
                {{ __('Сохранить') }}
            </el-button>
            </div>
    </form-row>
</template>

<script>

import EmployeeRepository from '@/repositories/employee';
import Employee from '@/models/employee';
import SearchMixin from '@/components/doctor/appointment/mixins/search';
import ManageMixin from '@/mixins/manage';
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    mixins:[
        SearchMixin,
        ManageMixin,
    ],
    props: {
        model: Object,
    },
    data() {
        return {
            loading: false,
            filter: {
                fullname: '',
            },
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.model.employees,
                });
            }),
            selectedEmployee: {},
            employees: new EmployeeRepository(),
            fields: [
                {
                    name: 'fullname',
                    title: __('ФИО'),
                    width: '50%',
                    filterField: 'fullname',
                },
                {
                    name: 'action',
                    title: __('Действие'),
                    width: '50%',
                },
            ],
        }
    },
    methods: {
        addEmployee() {
            let exist = this.model.employees.filter(item => item.id === this.selectedEmployee.id);
            if (_.isFilled(exist)) {
                this.$warning(__('Данный учасник уже участвует в согласовании'));
            } else {
                let user = new Employee({id: this.selectedEmployee.id});
            user.fetch().then(res => {
                this.model.employees.push({
                    id: res.response.data.id,
                    fullname: res.response.data.full_name
                });
            });
            }
        },
        removeEmployee(id) {
            let index = this.model.employees.findIndex((employee) => {
                    return employee.id === id;
                });
            if (index !== -1) {
                this.model.employees.splice(index, 1);
            }

        },
        cancel() {
            this.$emit('close');
        },
        save() {
            this.loading = true;
            this.model.save().then((response) => {
                this.$info(__('Сохраненно!'));
                this.$emit('close');
            });
            
        },
    },
}   
</script>


