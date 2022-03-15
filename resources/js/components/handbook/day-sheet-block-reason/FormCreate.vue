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
                @click.prevent="create" >
                {{ __('Добавить') }}
            </el-button>
        </div>
    </day-sheet-block-reason-form>
</template>

<script>
import DaySheetBlockReasonForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import DaySheetTimeBlockReason from '@/models/day-sheet/time-block-reason';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        DaySheetBlockReasonForm,
    },
    data() {
        return {
            model: new DaySheetTimeBlockReason(),
        }
    },
}
</script>
