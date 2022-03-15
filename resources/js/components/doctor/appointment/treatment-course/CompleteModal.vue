<template>
    <div>
        <div>
            <el-row :gutter="20">
                <el-col :span="16" :offset="4">
                    <form-date
                        :entity="course"
                        property="end"
                        :label="__('Поставить дату окончания курса')"
                        placeholder=""
                        :options="pickerOptions"
                        :clearable="true"
                        :editable="true" />
                </el-col>
            </el-row>
        </div>
        <div class="form-footer text-right mt-0">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="complete">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import DatepickerMixin from '@/mixins/datepicker';

export default {
    mixins: [
        DatepickerMixin,
    ],
    props: {
        course: Object,
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        complete() {
            this.$emit('complete');
        },
    },
}
</script>