<template>
    <div class="calendar-header">
        <div class="nav">
            <div class="calendar-buttons">
                <span 
                    :disabled="!headerProps.previousPeriod"
                    type="primary"
                    @click.prevent="setShowDate(headerProps.previousPeriod)">
                    <svg-icon name="arrow-back" class="icon-large" />
                </span>
                <span 
                    :disabled="!headerProps.nextPeriod"
                    type="primary"
                    @click.prevent="setShowDate(headerProps.nextPeriod)">
                    <svg-icon name="arrow-back" class="icon-large rotate-opposite" />    
                </span>
            </div>
            <el-button
                    v-if="showToday"
                    type="primary"
                    @click.prevent="setShowToday()">
                {{ __('Сегодня') }}
            </el-button>
        </div>
        <div class="label period-label">
            <span>{{ headerProps.periodLabel }}</span>
        </div>
        <div
                v-if="showPeriodSelect"
                class="period">
            <el-radio-group v-model="selectedPeriod">
                <el-radio-button label="week">{{ __('Неделя') }}</el-radio-button>
                <el-radio-button label="month">{{ __('Месяц') }}</el-radio-button>
                <el-radio-button label="year">{{ __('Год') }}</el-radio-button>
            </el-radio-group>
        </div>
        <slot name="header-actions"/>
    </div>
</template>

<script>
export default {
    props: {
        period: {
            type: String,
            default: 'week',
        },
        headerProps: {
            type: Object,
            required: true,
        },
        showPeriodSelect: {
            type: Boolean,
            default: true,
        },
        showToday: {
            type: Boolean,
            default: true,
        },
    },
    data() {
        return {
            selectedPeriod: 'week',
        };
    },
    mounted() {
        this.selectedPeriod = this.period;
    },
    methods: {
        setShowDate(d) {
            this.$emit('dateChanged', d);
        },
        setPeriod(p) {
            this.$emit('periodChanged', p);
        },
        setShowToday() {
            this.setShowDate(new Date());
        },
    },
    watch: {
        selectedPeriod(val) {
            this.setPeriod(val);
        },
    },
};
</script>