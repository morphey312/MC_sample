<template>
    <div class="time-wrapper">
        <div class="switch-schedule-period">
            <div class="schedule-switcher header-datepicker">
                &nbsp;
            </div>
            <div class="schedule-switcher header-datepicker">
                <a href="#" @click.prevent="showDatePicker">
                    <svg-icon name="calendar" class="icon-tiny icon-blue" />
                </a>
                <el-date-picker
                    ref="datepicker"
                    v-model="date"
                    type="date"
                    :clearable="false"
                    :picker-options="pickerOptions"
                    value-format="yyyy-MM-dd"
                    @change="changeSheetsDate" />
            </div>
            <div class="delete-all-lists">
                <div @click="removeAllDaySheets">{{ __('Закрыть все листы') }}</div>
            </div>
        </div>
        <el-col
            ref="timeList"
            :span="1"
            class="time-block"
            :style="{left: left + 'px'}">
            <div
                v-for="(time, index) in timeRange"
                :key="index"
                class="time-item">
                {{ time }}
            </div>
        </el-col>
    </div>
</template>

<script>
export default {
    props: {
        timeRange:  {
            type: [Object, Array],
            required: true
        },
        left: {
            type: Number,
        },
    },
    data() {
        return {
            date: null,
            pickerOptions: {
                firstDayOfWeek: 1,
            },
        };
    },
    created() {
        this.listenScrollSchedule = (val) => {
            if (this.$refs.timeList) {
                this.$refs.timeList.$el.scrollTop = val;    
            }
        }
    },
    mounted() {
        this.$eventHub.$on('scroll-schedule', this.listenScrollSchedule); 
    },
    beforeDestroy() {
        this.$eventHub.$off('scroll-schedule', this.listenScrollSchedule);
    },
    methods: {
        removeAllDaySheets() {
            this.$eventHub.$emit('remove-all-daysheets');
        },
        showDatePicker() {
            this.$nextTick(() => {
                this.$refs.datepicker.focus();
            });
        },
        changeSheetsDate() {
            this.$emit('change-sheets-date', this.date);
        },
    }
}
</script>