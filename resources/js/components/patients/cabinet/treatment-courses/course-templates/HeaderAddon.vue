<template>
     <div class="buttons">
        <a
            :disabled="!actionAllowed"
            @click.prevent="sign"
            class="mr-20">
            <svg-icon name="sign-alt" class="icon-small icon-blue">
                {{ __('Подписать') }}
            </svg-icon>
        </a>
        <a
            v-if="document.signatures.length !== 0"
            @click.prevent="showSign"
            class="mr-20">
            <svg-icon name="sign-alt" class="icon-small icon-blue">
                {{ __('Подписанты') }}
            </svg-icon>
        </a>
        <a 
            href="#"
            :disabled="!actionAllowed"
            @click.prevent="download"
            class="mr-20">
            <svg-icon name="download" class="icon-small icon-blue">
                {{ __('Скачать') }}
            </svg-icon>
        </a>
        <a 
            href="#"
            :disabled="!actionAllowed"
            @click.prevent="print">
            <svg-icon name="print" class="icon-tiny icon-blue">
                {{ __('Печатать') }}
            </svg-icon>
        </a>
     </div>
</template>
<script>
export default {
    props: {
        initialAllowed: {
            type: Boolean,
            default: false,
        },
        document: {
            type: Object,
        },
    },
    data() {
        return {
            actionAllowed: this.initialAllowed,
        }
    },
    methods: {
        sign() {
            if (this.actionAllowed) {
                this.$emit('sign');
            }
            return false;
        },
        download() {
            if (this.actionAllowed) {
                this.$emit('download');
            }
            return false;
        },
        print() {
            if (this.actionAllowed) {
                this.$emit('print');
            }
            return false;
        },
        allowActions(val = true) {
            this.actionAllowed = val;
        },
        showSign() {
            this.$emit('showSign', this.document);
        },
    },
}
</script>