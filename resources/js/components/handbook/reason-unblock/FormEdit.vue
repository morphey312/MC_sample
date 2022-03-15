<template>
    <day-sheet-unblock-reason-form :model="model">
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
    </day-sheet-unblock-reason-form>
</template>

<script>
import DaySheetUnblockReasonForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    components: {
        DaySheetUnblockReasonForm
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
