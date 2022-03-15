<template>
    <section>
        <ehealth-address
            v-model="model.address"
            error-prefix="address."
            :countryLock="true"
            :is-required="true"/>
    </section>
</template>

<script>
import EhealthAddress from '@/components/general/form/AddressEhealth.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        EhealthAddress,
    },
    props: {
        model: {
            type: Object
        },
    },
    mounted() {
        if (!_.isFilled(this.model.address.country_code)) {
            this.model.address.country_code = CONSTANTS.COUNTRY_CODE.UA
        }
    }
};
</script>
