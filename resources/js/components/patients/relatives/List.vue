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
                return Promise.resolve({
                    rows: this.patient.relatives
                })
            }),
            fields: [
                {
                    name: 'full_name',
                    title: __('Родственник'),
                    width: '50%',
                },
                {
                    name: 'relation',
                    title: __('Родственное отношение'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$handbook.getOption('patient_relatives', value);
                    }
                },
                {
                    name: 'is_granted',
                    title: __('Доступен в ЛК'),
                    width: '20%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
            ],
        }
    },
    methods: {
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        refresh() {
            this.$refs.table.refresh();
        }
    },
}
</script>