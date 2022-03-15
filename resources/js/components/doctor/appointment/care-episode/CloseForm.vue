<template>
    <close-episode-form :model="model">
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
                {{ __('Закрыть') }}
            </el-button>
            <el-button
                type="primary"
                @click="updateAndSubmit">
                {{ __('Закрыть и отправить в e-Health') }}
            </el-button>
        </div>
    </close-episode-form>
</template>
<script>
import CloseEpisodeForm from './FormForClose.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        CloseEpisodeForm,
    },
    props: {
        model: Object,
    },
    methods: {
        close() {
            this.model.isCancel = false;
            this.model.isClose = true;
            this.update();
        },
        updateAndSubmit() {}
    }
}
</script>
