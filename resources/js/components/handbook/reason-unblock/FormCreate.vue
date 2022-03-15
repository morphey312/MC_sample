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
                @click.prevent="create" >
                {{ __('Добавить') }}
            </el-button>
        </div>
    </day-sheet-unblock-reason-form>
</template>

<script>
import DaySheetUnblockReasonForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';
import DaySheetTimeUnBlockReason from '@/models/reasonunblock';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        DaySheetUnblockReasonForm,
    },
    data() {
        return {
            model: new DaySheetTimeUnBlockReason(),
        }
    },
}
</script>
