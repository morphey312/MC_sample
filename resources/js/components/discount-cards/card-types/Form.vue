<template>
    <model-form :model="model">
        <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :lazy="true"
                :label="__('Общее')"
                name="info" >
                <section>
                    <type-info :model="model" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :disabled="model.cant_be_copied"
                :label="__('Настройки передачи карты')"
                name="copy-settings" >
                <section>
                    <type-copyeng :model="model" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :disabled="!model.use_detail_payments"
                :label="__('Скидки по назначению платежа')"
                name="payment-destinations" >
                <section>
                    <payment-destinations :model="model" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Клиники')"
                name="clinics" >
                <section>
                    <clinics :model="model" />
                </section>
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons" />
    </model-form>
</template>

<script>
import TypeInfo from './form-tabs/Info.vue';
import TypeCopyeng from './form-tabs/Copyeng.vue';
import PaymentDestinations from './form-tabs/PaymentDestinations.vue';
import Clinics from './form-tabs/Clinics.vue';

export default {
    components: {
        TypeInfo,
        TypeCopyeng,
        PaymentDestinations,
        Clinics,
    },
    props: {
        model: Object
    },
    data() {
        return {
            activeTab: 'info',
        };
    },
}   
</script>