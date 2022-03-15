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
export default {
    props: {
        repository: Object,
    },
    data() {
        return {
            fields: [
                {
                    name: 'company.name',
                    title: __('Название страховой'),
                    dataClass: 'no-dash',
                    width: '35%',
                }, 
                {
                    name: 'number',
                    title: __('Номер полиса'),
                    dataClass: 'no-dash text-right',
                    width: '25%',
                },
                {
                    name: 'expires',
                    title: __('Срок действия полиса'),
                    dataClass: 'no-dash',
                    width: '20%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                },
                {
                    name: 'is_valid',
                    title: __('Действует'),
                    width: '20%',
                    formatter: (val) => {
                        return this.$formatter.boolToString(val, '<span class="check-yes" />');
                    },
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
    },
}
</script>