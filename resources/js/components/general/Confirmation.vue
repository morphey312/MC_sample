<template>
    <modal
        :header="title"
        :visible="visible"
        :width="width"
        @close="close"
        @closed="closed">
        <div class="sections-wrapper">
            <section class="confirmation-message" v-html="message" />
            <template v-if="doubleConfirmMessage !== null">
                <hr />
                <section>
                    <el-checkbox 
                        class="wrappable"
                        v-model="doubleConfirmed">
                        {{ doubleConfirmMessage }}
                    </el-checkbox>
                </section>
            </template>
            <div class="dialog-footer text-right">
                <el-button
                    @click="close">
                    {{ cancelBtnText }}
                </el-button>
                <el-button
                    type="primary"
                    :disabled="!doubleConfirmed"
                    @click="confirm">
                    {{ confirmBtnText }}
                </el-button>
            </div>
        </div>
    </modal>
</template>

<script>
import Modal from '@/components/general/Modal.vue';

export default {
    components: {
        Modal
    },
    props: {
        title: {
            type: String,
            default: __('Подтвердите действие'),
        },
        confirmBtnText: {
            type: String,
            default: __('Подтвердить'),
        },
        cancelBtnText: {
            type: String,
            default: __('Отменить'),
        },
        message: {
            type: String,
            default: __('Вы уверены что хотите продолжить?'),
        },
        doubleConfirmMessage: {
            type: String,
            default: null,
        },
        confirmed: {
            type: Function,
            required: true,
        },
        cancelled: {
            type: Function,
        },
        width: {
            type: String,
            default: '50%'
        }
    },
    data() {
        return {
            visible: false,
            wasConfirmed: false,
            doubleConfirmed: this.doubleConfirmMessage === null,
        };
    },
    mounted() {
        document.body.appendChild(this.$el);
        this.visible = true;
    },
    methods: {
        close() {
            this.visible = false;
            if (this.wasConfirmed) {
                this.confirmed();
            } else if (this.cancelled) {
                this.cancelled();
            }
        },
        confirm() {
            this.wasConfirmed = true;
            this.close();
        },
        closed() {
            document.body.removeChild(this.$el);
            this.$destroy();
        },
    },
};
</script>