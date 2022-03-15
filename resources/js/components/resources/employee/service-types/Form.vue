<template>
    <model-form :model="model">
        <form-select
            key="service_id"
            :entity="model"
            :options="serviceTypes"
            property="service_id"
            :label="__('Вид услуги')"
            popper-class="select-table">
            <select-table-header 
                slot="picker-header"
                :fields="serviceFields" />
            <template 
                slot="picker-item" 
                slot-scope="props">
                <select-table-row 
                    :option="props.option"
                    :fields="serviceFields" />
            </template>
        </form-select>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ServiceTypeRepository from '@/repositories/clinic/service-type';
import SelectTableHeader from '@/components/general/form/select/TableHeader.vue';
import SelectTableRow from '@/components/general/form/select/TableRow.vue';

export default {
    components: {
        SelectTableHeader,
        SelectTableRow,
    },
    props: {
        model: Object,
        employee: Object,
    },
    data() {
        return {
            serviceTypes: new ServiceTypeRepository({
                filters: {
                    available_for_employee: this.employee.id,
                    active: true,
                }
            }),
            serviceFields: [
                {
                    name: 'speciality_name',
                    label: __('Специализация'),
                    width: '200px',
                },
                {
                    name: 'providing_condition',
                    label: __('Условия предоставления'),
                    width: '150px',
                    formatter: (value) => {
                        return this.$handbook.getOption('service_providing_conditions', value);
                    },
                },
                {
                    name: 'clinic_name',
                    label: __('Клиника'),
                    width: '150px',
                },
            ],
        }
    }
}
</script>