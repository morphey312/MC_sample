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
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </channel-form>
</template>

<script>
import EditMixin from '@/mixins/generic-edit';
import ChannelForm from './Form.vue';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        ChannelForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
}
</script>