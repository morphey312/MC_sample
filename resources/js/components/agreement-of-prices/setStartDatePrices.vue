<template>
    <div>
        <el-row :gutter="20">
            <el-col :span="16" :offset="4">
                <form-date
                    :entity="attributes"
                    property="date_pass"
                    :label="__('Дата начала действия тарифа')"
                    :placeholder="__('Дата действия')"
                    :options="pickerOptions"
                    :clearable="true"
                    :editable="true" />
            </el-col>
        </el-row>
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

export default {
  
    data() {
        return {
            attributes: {
                date_pass: this.getInitialDate(),
            },
            pickerOptions: {
                disabledDate: this.checkDisabledDate,
                firstDayOfWeek: 1,
            }
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
        checkDisabledDate(date) {
            if (this.$can('price-agreement-acts.full-date-confirm')) {
                return null;
            }
            return this.$moment(date).isBefore(this.$moment().add(3, "days"));
        },
    },
}
</script>
