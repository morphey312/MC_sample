<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @loaded="loaded"
        @selection-changed="selectionChanged" >
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        patient: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return  Promise.resolve({
                    rows: this.getList(),
                });
            }),
            fields: [
                {
                    name: 'company.name',
                    title: __('Страховая компания'),
                    width: '25%',
                },
                {
                    name: 'number',
                    title: __('Номер полиса'),
                    width: '15%',
                },
                {
                    name: 'expires',
                    title: __('Срок действия'),
                    width: '15%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'is_valid',
                    title: __('Действует'),
                    width: '15%',
                    formatter: (val) => {
                        return this.$formatter.boolToString(val, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'comment',
                    title: __('Примечание'),
                    width: '30%',
                },
            ],
            selectedIndex: null,
        }
    },
    mounted() {
        this.$watch('patient.insurance_policies', () => {
            this.$refs.table.refresh();
        }, { deep: true });
    },
    methods: {
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.getSelectedIndex();
            this.$emit('selection-changed', selection);
        },
        getSelectedIndex() {
            this.selectedIndex = this.$refs.table.selectedRow ? this.$refs.table.selectedRow.index : null;
        },
        getList() {
            return this.patient.insurance_policies;
        },
    },
}
</script>