<template>
    <channel-form :model="model">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button 
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </channel-form>
</template>

<script>
import CreateMixin from '@/mixins/generic-create';
import ChannelForm from './Form.vue';
import NotificationChannel from '@/models/notification/channel';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ChannelForm,
    },
    data() {
        return {
            model: new NotificationChannel(),
        }
    },
}
</script>