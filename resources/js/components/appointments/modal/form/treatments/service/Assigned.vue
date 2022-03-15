<template>
    <section>
        <manage-table
            ref="table"
            :fields="fields"
            :repository="repository"
            table-height="auto"
            :enable-pagination="false"
            @selection-changed="checked" />
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="confirm"
                :disabled="disableSelect">
                {{ __('Выбрать') }}
            </el-button>
        </div>
    </section>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        services: Array,
        withPolicy: {
            type: Boolean,
            default: false,
        },
        insurancePolicy: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            rows: [],
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.services,
                });
            }),
            fields: [
                {
                    name: '__checkbox',
                    titleClass: 'text-center',
                    dataClass: 'text-center',
                    width: '5%',
                },
                {
                    name: 'name',
                    title: __('Название услуги'),
                    width: '25%',
                },
                {
                    name: 'clinic_name',
                    title: __('Клиника назначения'),
                    width: '10%',
                },
                {
                    name: 'quantity',
                    title: __('Количество услуг'),
                    width: '10%',
                    dataClass: 'text-right',
                },
                {
                    name: 'assigner_name',
                    title: __('Врач'),
                    width: '21%',
                },
                {
                    name: 'assignment_date',
                    title: __('Дата назначения'),
                    width: '15%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                ...(this.withPolicy ? [
                {
                    name: 'by_policy',
                    title: __('Полис'),
                    width: '10%',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'franchise',
                    title: __('Фр-за, %'),
                    width: '10%',
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                ] : [])
            ],
        };
    },
    computed: {
        disableSelect() {
            return this.rows.length === 0;
        },
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        checked(rows = []) {
            this.rows = rows;
        },
        confirm() {
            let indexList = this.$refs.table.getSelectedRows();
            let list = this.$refs.table.getData().filter((item) => {
                return indexList.indexOf(item.id) !== -1;
            });

            if (this.insurancePolicy == null) {
                let policyAssignments = list.filter(item => item.by_policy);
                if (policyAssignments.length != 0) {
                    return this.$error(__('Услуга назначена по страховой, добавьте полис в запись'));
                }
            }

            this.$emit('selected', list);
        },
    }
}
</script>
