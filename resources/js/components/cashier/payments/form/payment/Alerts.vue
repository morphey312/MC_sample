<template>
    <div class="alerts">
        <template v-if="patient">
            <alert
                type="do_not_take_payment"
                v-if="doNotTakePayment">
                {{ __('Внимание! За некоторые услуги оплату не брать!') }}
            </alert>
        </template>
    </div>
</template>
<script>
export default {
    props: {
        patient: {
            type: Object,
            default: () => ({}),
        },
        doNotTakePayment: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {

        }
    }
}
</script>
