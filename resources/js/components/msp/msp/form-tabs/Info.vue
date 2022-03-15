<template>
    <div class="msp-info">
        <section>
            <el-row :gutter="20">
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="name"
                        :label="__('Название')" />
                    <form-select
                        :entity="model"
                        :disabled="mspCommited"
                        options="msp_type"
                        property="type"
                        :label="__('Тип учреждения')" />
                    <form-input
                        :entity="model"
                        :disabled="mspCommited"
                        property="edrpou"
                        :label="__('Код ЕГРПОУ')" />
                    <form-input
                        :entity="model"
                        :disabled="mspCommited"
                        property="checking_account"
                        :label="__('Счет')" />
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="phone"
                        :label="__('Номер телефона')" />
                    <form-input
                        :entity="model"
                        property="additional_phone"
                        :label="__('Дополнительный номер телефона')" />
                    <form-input
                        :entity="model"
                        property="email"
                        :label="__('E-mail')" />
                    <form-input
                        :entity="model"
                        :disabled="mspCommited"
                        property="bank"
                        :label="__('Банк')" />
                </el-col>
                <el-col :span="8">
                    <form-input
                        :entity="model"
                        property="website"
                        :label="__('Адрес веб-сайта')" />
                    <form-input
                        :entity="model"
                        property="receiver_funds_code"
                        :label="__('Код получателя гос. средств')" />
                    <form-input
                        :entity="model"
                        property="beneficiary"
                        :label="__('Бенефициар')" />
                </el-col>
            </el-row>
        </section>
        <hr />
        <section>
            <ehealth-address
                v-model="model.address"
                error-prefix="address." />
        </section>
    </div>
</template>

<script>
import EhealthAddress from '@/components/general/form/AddressEhealth.vue';

export default {
    components: {
        EhealthAddress,
    },
    props: {
        model: {
            type: Object
        },
    },
    computed: {
        mspCommited() {
            return _.isFilled(this.model.ehealth_id);
        }
    }
}
</script>
