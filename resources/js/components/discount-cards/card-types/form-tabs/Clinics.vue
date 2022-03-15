<template>
    <form-row name="clinics">
        <transfer-table
            v-if="model.loading === false"
            :items="clinics"
            :fields="clinicFields"
            v-model="model.clinics"
            value-key="clinic_id"
            :left-title="__('Клиника')"
            left-width="280px"
            :right-title="__('Клиника')"
            right-width="280px"
            @new-row="initClinic">
            <template slot="payment_method_id" slot-scope="props">
                <form-select
                    :entity="model.clinics[props.rowIndex]"
                    :options="props.rowData.payment_methods"
                    property="payment_method_id"
                    :clearable="true"
                    control-size="mini"
                    css-class="table-row" />
            </template>
        </transfer-table>
    </form-row>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        model: Object,
    },
    data() {
        return {
            placeholder: this.getPlaceholder(),
            clinics: new ClinicRepository({
                accessLimit: this.$isAccessLimited('discount-card-types')
            }),
            clinicFields: [
                {
                    name: 'payment_method_id',
                    title: __('Вид оплаты'),
                    width: '200px',
                },
            ],
        }
    },
    methods: {
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Добавьте клиники слева'));
        },
        initClinic(data) {
            data.payment_method_id = null;
        },
    },
}
</script>
