<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-input-i18n
                            :entity="model"
                            property="name"
                            :label="__('Название')" />
                        <div class="d-flex">
                            <form-checkbox
                                :entity="model"
                                property="use_cash"
                                :label="__('Наличные средства')" />
                            <svg-icon
                                name="info-alt"
                                class="icon-tiny icon-grey ml-10"
                                :title="__('Данная форма оплаты означает, что это наличные денежные средства. Остатки по наличным средствам рассчитываются во Вкладке Остатки отдельным списком. Также на них можно положить разменную монету и можно также осуществлять Служебное извлечение средств из кассы.')" />
                        </div>
                        <form-checkbox
                            :entity="model"
                            property="change_payment_date"
                            :label="__('Возможность изменения даты платежа')" />
                    </el-col>
                    <el-col :span="12">
                        <form-row
                            name="for_is_enabled"
                            label="&nbsp">
                            <form-checkbox
                                :entity="model"
                                property="is_enabled"
                                :label="__('Активная')" />
                        </form-row>
                        <form-checkbox
                            :entity="model"
                            property="online_payment"
                            :label="__('Для оплат на сайте')" />
                        <form-checkbox
                            :entity="model"
                            property="pc_payment"
                            :label="__('Для оплат c ЛК')" />
                    </el-col>
                </el-row>
            </section>
            <section>
                <form-row name="clinics">
                    <transfer-table
                        v-if="model.loading === false"
                        :items="clinics"
                        :fields="clinicFields"
                        v-model="model.clinics"
                        value-key="clinic_id"
                        :left-title="__('Клиника')"
                        left-width="200px"
                        :right-title="__('Клиника')"
                        right-width="200px"
                        @new-row="initClinic">
                        <template slot="is_fiscal" slot-scope="props">
                            <form-checkbox
                                :entity="props.rowData.data"
                                property="is_fiscal"
                                control-size="mini"
                                css-class="table-row" />
                        </template>
                    </transfer-table>
                </form-row>
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        model: Object
    },
    data() {
        return {
            placeholder: this.getPlaceholder(),
            clinics: new ClinicRepository(),
            clinicFields: [
                {
                    name: 'is_fiscal',
                    title: __('Фискальная оплата'),
                    width: '150px',
                },
            ],
        }
    },
    methods: {
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Добавьте клиники слева'));
        },
        initClinic(data) {
            data.is_fiscal = false;
        },
    }
}
</script>
