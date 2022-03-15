<template>
    <div>
        <div>
            <el-row :gutter="20">
                <el-col :span="16" :offset="4">
                    <form-date
                        :entity="attributes"
                        property="date_pass"
                        :label="__('Поставить дату сдачи')"
                        :placeholder="__('Дата сдачи')"
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
                @click="save">
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
    data() {
        return {
            attributes: {
                date_pass: this.getInitialDate(),
            },
        };
    },
    methods: {
        getInitialDate() {
            return this.maxDate ? this.maxDate : null;
        },
        cancel() {
            this.$emit('cancel');
        },
        save() {
            this.$emit('save', this.attributes.date_pass);
        },
    },
}
</script>