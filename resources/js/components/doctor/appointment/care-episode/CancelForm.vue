<template>
    <cancel-episode-form :model="model">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="close">
                {{ __('Удалить') }}
            </el-button>
            <el-button
                type="primary"
                @click="updateAndSubmit">
                {{ __('Удалить и отправить в e-Health') }}
            </el-button>
        </div>
    </cancel-episode-form>
</template>
<script>
import CancelEpisodeForm from './FormForCancel.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        CancelEpisodeForm,
    },
    props: {
        model: Object,
    },
    methods: {
        close() {
            this.model.isCancel = true;
            this.model.isClose = false;
            this.update();
        },
        updateAndSubmit() {}
    }
}
</script>
