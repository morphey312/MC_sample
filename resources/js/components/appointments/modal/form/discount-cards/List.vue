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
        patient: Object,
    },
    data() {
        return {
            fields: [
                {
                    name: 'type.name',
                    title: __('Тип карты'),
                    dataClass: 'no-dash',
                }, 
                {
                    name: 'type.discount_percent',
                    title: __('% скидки'),
                    dataClass: 'no-dash text-right',
                },
                {
                    name: 'card_number',
                    title: __('№ карты'),
                    dataClass: 'no-dash', 
                },
                {
                    name: 'patients',
                    title: __('Не действует'),
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(this.isDisabled(value), '<span class="check-yes" />');
                    },
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                    dataClass: 'no-dash',
                },
                {
                    name: 'valid_from',
                    title: __('Действительна с:'),
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'expires',
                    title: __('Действительна до:'),
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
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
        isDisabled(list) {
            let patient = list.find(item => item.patient_id == this.patient.id);
            return patient.disabled;
        },
    },
}
</script>