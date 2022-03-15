<template>
    <close-encounter-form :model="model">
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
    </close-encounter-form>
</template>
<script>
import CloseEncounterForm from './FormForClose.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        CloseEncounterForm,
    },
    props: {
        model: Object,
    },
    methods: {
        close() {
            this.model.isClose = true;
            this.update();
        },
        updateAndSubmit() {

        }
    }
}
</script>
