<template>
    <div class="sections-wrapper">
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="contact_phone"
                        :label="__('Контактный телефон')">
                        <svg-icon
                            slot="label-addon"
                            name="question-alt"
                            class="icon-tiny"
                            :title="__('Используется в ЛК')" />
                    </form-input>
                    <form-input
                        :entity="model"
                        property="additional_contact_phone"
                        :label="__('Дополнительный телефон')">
                        <svg-icon
                            slot="label-addon"
                            name="question-alt"
                            class="icon-tiny"
                            :title="__('Используется в ЛК')" />
                    </form-input>
                    <form-input
                        :entity="model"
                        property="email"
                        :label="__('Email')" />
                    <form-row
                        name="latlng"
                        :label="__('Координаты (широта/долгота)')">
                        <div class="form-input-group">
                            <form-input
                                :entity="model"
                                property="lat" />
                            <form-input
                                :entity="model"
                                property="lng" />
                        </div>
                    </form-row>
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        
                        property="money_reciever.signer"
                        :label="__('ФИО подписанта')">
                        <svg-icon
                            slot="label-addon"
                            name="question-alt"
                            class="icon-tiny"
                            :disabled="true"
                            :title="__('Используется в официальных документах (формы МОЗ, акты и т.д.)')" />
                    </form-input>
                    <form-input
                        :entity="model"
                        property="money_reciever.signer_position"
                        :label="__('Должность подписанта')">
                        <svg-icon
                            slot="label-addon"
                            name="question-alt"
                            class="icon-tiny"
                            :disabled="true"
                            :title="__('Используется в официальных документах (формы МОЗ, акты и т.д.)')" />
                    </form-input>
                    <form-select
                        :entity="model"
                        options="clinic_kind"
                        property="kind"
                        :label="__('Тип подразделения')" />
                    <form-upload
                        :entity="model"
                        :multiple="false"
                        accept="image/jpeg,image/png"
                        property="image_id"
                        :label="__('Фото клиники')">
                        <svg-icon
                            slot="label-addon"
                            name="question-alt"
                            class="icon-tiny"
                            :title="__('Используется в ЛК')" />
                    </form-upload>
                </el-col>
                <el-col :span="8">
                    <schedule
                        :label="__('Режим работы')"
                        v-model="model.working_hours" />
                    <form-text
                        :entity="model"
                        property="money_reciever.official_additional"
                        :label="__('Реквизиты')">
                        <svg-icon
                            slot="label-addon"
                            name="question-alt"
                            class="icon-tiny"
                            :disabled="true"
                            :title="__('Используется в акте выполненых работ')" />
                    </form-text>
                    <form-input
                        :entity="model"
                        property="authority_name"
                        :label="__('Название органа управления')" />
                </el-col>
            </el-row>
        </section>
        <template v-if="isOutpatient">
            <hr />
            <section>
                <h3>{{ __('Адрес регистратуры') }}</h3>
                <ehealth-address
                    v-model="model.reception_address"
                    error-prefix="reception_address."
                    :is-required="false"
                    :show-apartment="false" />
            </section>
        </template>
    </div>
</template>

<script>
import Schedule from '@/components/general/form/Schedule.vue';
import EhealthAddress from '@/components/general/form/AddressEhealth.vue';
import CONSTANT from '@/constants';

export default {
    components: {
        Schedule,
        EhealthAddress,
    },
    props: {
        model: Object,
        mspType: String,
    },
    computed: {
        isOutpatient() {
            return this.mspType === CONSTANT.EHEALTH.MSP_TYPE.OUTPATIENT;
        },
    },
}
</script>
