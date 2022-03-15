<template>
    <day-sheet-block-reason-form :model="model">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </day-sheet-block-reason-form>
</template>

<script>
import DaySheetBlockReasonForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    components: {
        DaySheetBlockReasonForm
    },
    mixins: [
        EditMixin,
    ],
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
