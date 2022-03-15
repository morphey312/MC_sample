<template>
    <manage-table
        ref="table"
        :fields="fields"
        :scopes="scopes"
        :repository="repository"
        :filters="filters"
        :initial-sort-order="initialSortOrder"
        :flex-height="true"
        :selectable-rows="true"
    >
        <template slot="service.name"
                  slot-scope="props">
            {{ getNameOfCheck(props.rowData )}}
        </template>
        <template slot="print"
                  slot-scope="props">
            <a
                @click.prevent="showCheck(props.rowData.check)">
                <svg-icon name="print" class="icon-blue" />
            </a>
        </template>
    </manage-table>
</template>

<script>
import PaymentRepository from "@/repositories/payment";
import printer from '@/services/print';
import CONSTANTS from '@/constants';

export default {
    props: {
        patient: Object,
    },
    data() {
        return {
            repository: new PaymentRepository(),
            fields: [
                {
                    name: 'service.name',
                    title: __('Услуга'),
                    dataClass: 'no-dash',
                    width: '60%',
                },
                {
                    name: 'created',
                    width: '20%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filterField: 'date',
                    title: __('Дата'),
                    dataClass: 'no-dash',
                },
                {
                    name: 'print',
                    title: __('Печать'),
                    dataClass: 'no-dash',
                    width: '10%',
                },
            ],
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
            scopes: [
                'check',
            ],
            filters: {
                patient: this.patient.id,
                checkBody: true,
                or: {
                    have_service: false,
                    is_prepayment: false,
                },
            },
        }
    },
    methods: {
        getNameOfCheck(check) {
            if(check.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
                return  __('Возврат за услугу {service}',{service: check.service.name});
            }
            if (check.service) {
                return check.service.name;
            }
            return __('Предоплата за услугу');
        },
        showCheck(check) {
            printer.newPrinter().printRawHtml("<div style='margin-top: 0; width: 277px'>" + check.body + "</div>");
        }
    }
}
</script>
