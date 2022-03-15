<template>
    <episode-form :model="model">
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
            <el-button
                type="primary"
                @click="updateAndSubmit">
                {{ __('Сохранить и отправить в e-Health') }}
            </el-button>
        </div>
    </episode-form>
</template>

<script>
import EpisodeForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        EpisodeForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
    methods: {
        updateAndSubmit() {}
    }
}
</script>
