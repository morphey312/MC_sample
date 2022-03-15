<template>
    <div class="feedback">
        <form-input
            :entity="model"
            property="message"
            :label="__('Краткое описание проблемы')"
        />
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="model.message.length === 0"
                @click.prevent="send" >
                {{ __('Отправить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import logger from '@/services/logging';

export default {
    data() {
        return {
            model: {
                message: '',
            },
        };
    },
    methods: {
        cancel() {
            this.$emit('close');
        },
        send() {
            logger.sendMessage(this.model.message);
            this.model.message = '';
            this.$info(__('Сообщение отправлено'));
            this.$emit('close');
        },
    }
}
</script>