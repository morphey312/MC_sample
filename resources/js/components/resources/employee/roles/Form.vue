<template>
    <div>
        <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :label="__('Общее')"
                name="general">
                <model-form :model="model">
                    <section>
                        <el-row>
                            <el-col :span="6">
                                <form-input
                                    :entity="model"
                                    property="name"
                                    :label="__('Название группы')"
                                />
                            </el-col>
                        </el-row>
                    </section>
                    <hr />
                    <section>
                        <form-row name="permissions">
                            <transfer-table
                                v-if="model.loading === false"
                                key="permissions"
                                :items="permissions"
                                :has-groups="true"
                                :has-right-groups="true"
                                v-model="model.permissions"
                                height="300px"
                                :left-title="__('Доступные права')"
                                left-width="430px"
                                :right-title="__('Выбранные права')"
                                right-width="430px"
                                :right-filter="true">
                            </transfer-table>
                        </form-row>
                    </section>
                </model-form>
             </el-tab-pane>
             <el-tab-pane
                v-if="$can(['employees.update', 'employees.update-clinic'])"
                :lazy="true"
                :label="__('Сотрудники входящие в группу')"
                name="users">
                <section>
                    <form-row 
                        name="users"
                        css-class="form-input mb-0">
                        <transfer-table
                            v-if="model.loading === false"
                            key="users"
                            :items="users"
                            :fields="fields"
                            :items-fields="fields"
                            v-model="model.users"
                            height="400px"
                            :left-title="__('ФИО')"
                            left-width="145px"
                            :right-title="__('ФИО')"
                            right-width="145px"
                            :right-filter="true">
                        </transfer-table>
                    </form-row>
                </section>
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons"></slot>
    </div>
</template>

<script>
import PermissionRepository from '@/repositories/permission';
import UserRepository from '@/repositories/user';
import ProxyRepository from '@/repositories/proxy-repository';
import ClinicRepository from '@/repositories/clinic';
import PositionRepository from '@/repositories/employee/position';
import Employee from '@/models/employee';
import CONSTANT from '@/constants';

export default {
    props: {
        model: Object,
        tab: {
            type: String,
            default: 'general',
        },
    },
    data() {
        let users = new UserRepository();
        return {
            permissions: new PermissionRepository(),
            users: new ProxyRepository(() => {
                return users.fetch({type: CONSTANT.USER.TYPE.EMPLOYEE}, [], ['employee'], 1, 1000).then((result) => {
                    return Promise.resolve(result.rows.map((row) => ({
                        id: row.id,
                        value: row.userable.full_name,
                        itemdata: new Employee(row.userable),
                    })));
                });
            }),
            activeTab: this.tab,
            fields: [
                {
                    name: 'itemdata.clinic_names',
                    title: __('Клиника'),
                    width: '95px',
                    editable: false,
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new ClinicRepository(),
                    filterField: 'itemdata.clinic_ids',
                },
                {
                    name: 'itemdata.position_names',
                    title: __('Должность'),
                    width: '95px',
                    editable: false,
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    },
                    filter: new PositionRepository(),
                    filterField: 'itemdata.position_ids',
                },
                {
                    name: 'itemdata.status_names',
                    title: __('Статус'),
                    width: '95px',
                    editable: false,
                    formatter: (value) => {
                        return this.$formatter.fromHandbook('employee_status', value);
                    },
                    filter: 'employee_status',
                },
            ],
        };
    },
    watch: {
        tab(val) {
            this.activeTab = val;
        },
        activeTab(val) {
            this.$emit('tab-changed', val);
        },
    },
}
</script>