<template>
    <div>
        <form-select 
            :entity="input"
            property="provider"
            :label="__('Выберите способ подписи')"
            :options="providers" />
        <component 
            v-if="input.provider && base64Data"
            ref="signProvider"
            :is="providerComponent"
            :data="base64Data"
            @signed="signed"
            @cancel="cancel" />
        <div
            v-else
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import DepositSign from './DepositSign.vue';

export default {
    props: {
        data: {
            type: [String, Blob, Object],
            required: true,
        },
    },
    data() {
        return {
            providers: [{
                id: 'depositsign',
                value: 'DepositSign',
                component: DepositSign,
            }],
            input: {
                provider: null,
            },
            base64Data: null,
        };
    },
    computed: {
        providerComponent() {
            return (_.find(this.providers, (p) => p.id === this.input.provider) || {}).component;
        },
    },
    beforeMount() {
        this.input.provider = this.providers[0].id;
        this.toBase64(this.data).then((data) => {
            this.base64Data = data;
        });
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        signed(data) {
            this.$emit('signed', data);
        },
        toBase64(data) {
            if (typeof data === 'string') {
                return Promise.resolve(this.encodeBase64(data));
            }
            if (data instanceof Blob) {
                return new Promise((resolve) => {
                    let reader = new FileReader();
                    reader.onload = () => {
                        resolve(btoa(reader.result));
                    }
                    reader.readAsBinaryString(data);
                });
            }
            return Promise.resolve(this.encodeBase64(JSON.stringify(data)));
        },
        encodeBase64(data) {
            return btoa(unescape(encodeURIComponent(data)));
        },
    },
}
</script>