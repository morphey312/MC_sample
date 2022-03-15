<template>
    <form-row 
        name="schedule"
        :label="label">
        <el-popover
            placement="bottom"
            width="350"
            trigger="manual"
            v-model="popoverVisible">
            <div 
                slot="reference"
                class="el-input el-input--medium">
                <input 
                    class="el-input__inner"
                    readonly 
                    :value="implodedValue"
                    @click="showPopover" />
            </div>
            <div class="schedule-picker">
                <div class="entry">
                    <el-checkbox-group 
                        v-model="days"
                        class="el-radio-group">
                        <el-checkbox-button 
                            v-for="weekday in weekdays" 
                            :label="weekday.day" 
                            :key="weekday.day"
                            :disabled="used_days.indexOf(weekday.day) !== -1"
                            class="el-radio-button">
                            {{ weekday.name }}
                        </el-checkbox-button>
                    </el-checkbox-group>
                    <el-row :gutter="20">
                        <el-col 
                            :span="breaks ? 12 : 24">
                            <label class="input-label">{{ __('Часы работы') }}</label>
                            <div class="form-input-group">
                                <el-time-select 
                                    v-model="from" 
                                    :placeholder="__('С')"
                                    :picker-options="pickerOptions" />
                                <el-time-select 
                                    v-model="to" 
                                    :placeholder="__('По')"
                                    :picker-options="pickerOptions" />
                            </div>
                        </el-col>
                        <el-col 
                            v-if="breaks"
                            :span="12">
                            <label class="input-label">{{ __('Перерыв') }}</label>
                            <div class="form-input-group">
                                <el-time-select 
                                    v-model="break_from" 
                                    :placeholder="__('С')"
                                    :picker-options="pickerOptions" />
                                <el-time-select 
                                    v-model="break_to" 
                                    :placeholder="__('По')"
                                    :picker-options="pickerOptions" />
                            </div>
                        </el-col>
                    </el-row>
                    <div class="text-right">
                        <el-button
                            :disabled="!canAdd"
                            @click="addHours">
                            {{ __('Добавить') }}
                        </el-button>
                    </div>
                </div>
                <hr />
                <ul 
                    v-if="val.strings.length !== 0"
                    class="list">
                    <li 
                        v-for="(str, index) in val.strings"
                        :key="index">
                        <span>{{ str }}</span>
                        <a 
                            href="#"
                            @click.prevent="deleteHours(index)">
                            {{ __('удалить') }}
                        </a>
                    </li>
                </ul>
                <div 
                    v-else
                    class="empty">
                    {{ __('Не задано') }}
                </div>
                <hr />
                <div class="buttons text-right">
                    <el-button
                        @click="cancel">
                        {{ __('Отмена') }}
                    </el-button>
                    <el-button
                        type="primary"
                        @click="apply">
                        {{ __('Применить') }}
                    </el-button>
                </div>
            </div>
        </el-popover>
    </form-row>
</template>

<script>
export default {
    props: {
        value: Object,
        label: {
            type: String,
        },
        breaks: {
            type: Boolean,
            default: true,
        },
    },
    computed: {
        canAdd() {
            return this.days.length !== 0 
                && _.isFilled(this.from)
                && _.isFilled(this.to)
                && this.from < this.to
                && (
                    _.isFilled(this.break_from) && 
                    _.isFilled(this.break_to) &&
                    this.break_from < this.break_to &&
                    this.break_from > this.from &&
                    this.break_to < this.to ||
                    _.isVoid(this.break_from) && 
                    _.isVoid(this.break_to)
                );
        },
        implodedValue() {
            return (this.value && (typeof this.value === 'object')) 
                ? this.value.strings.join('; ')
                : '';
        },
    },
    data() {
        return {
            popoverVisible: false,
            days: [],
            used_days: this.initUsedDays(this.value),
            val: this.initValue(this.value),
            weekdays: [
                {day: 1, name: __('Пн')},
                {day: 2, name: __('Вт')},
                {day: 3, name: __('Ср')},
                {day: 4, name: __('Чт')},
                {day: 5, name: __('Пт')},
                {day: 6, name: __('Сб')},
                {day: 7, name: __('Вс')},
            ],
            from: null,
            to: null,
            break_from: null,
            break_to: null,
            pickerOptions: {
                start: '00:00',
                step: '00:05',
                end: '23:55'
            },
        };
    },
    methods: {
        showPopover() {
            if (this.popoverVisible) {
                this.popoverVisible = false;
            } else {
                this.val = this.initValue(this.value);
                this.used_days = this.initUsedDays(this.value);
                this.popoverVisible = true;
            }
        },
        addHours() {
            this.used_days.push(...this.days);
            this.val.hours.push({
                days: this.days,
                from: this.from,
                to: this.to,
                break_from: this.break_from,
                break_to: this.break_to,
            });
            this.days = [];
            this.from = null;
            this.to = null;
            this.break_from = null;
            this.break_to = null;
            this.updateString();
        },
        deleteHours(index) {
            let deleted = this.val.hours[index];
            this.val.hours.splice(index, 1);
            this.used_days = this.used_days.filter((d) => deleted.days.indexOf(d) === -1);
            this.updateString();
        },
        updateString() {
            this.val.strings = this.val.hours
                .map((hours) => {
                    let intervals = [];
                    let start = null;
                    let end = null;
                    hours.days.sort().forEach((day) => {
                        if (start === null) {
                            start = day;
                            end = day;
                        } else if (day - end === 1) {
                            end = day;
                        } else {
                            intervals.push(this.getWeekDayRange(start, end));
                            start = day;
                            end = day;
                        }
                    });
                    if (start !== null) {
                        intervals.push(this.getWeekDayRange(start, end));
                    }
                    return intervals.join(', ') + ': ' +
                        `${hours.from}-${hours.to}` +
                        (hours.break_from ? __(', перерыв: {break}', {break: `${hours.break_from}-${hours.break_to}`}) : '');
                });
        },
        getWeekDay(index) {
            return (_.find(this.weekdays, wd => wd.day === index) || {}).name;
        },
        getWeekDayRange(start, end) {
            if (start === end) {
                return this.getWeekDay(start);
            } else {
                return [
                    this.getWeekDay(start),
                    this.getWeekDay(end),
                ].join(end - start > 1 ? '-' : ', ');
            }
        },
        apply() {
            this.$emit('input', this.val);
            this.popoverVisible = false;
        },
        cancel() {
            this.popoverVisible = false;
        },
        initValue(v) {
            if (v && (typeof v === 'object')) {
                return {
                    strings: [...v.strings],
                    hours: [...v.hours],
                };
            } else {
                return {
                    strings: [],
                    hours: [],
                };
            }
        },
        initUsedDays(v) {
            if (v && (typeof v === 'object')) {
                let res = [];
                v.hours.forEach((h) => {
                    res.push(...h.days);
                });
                return res;
            } else {
                return [];
            }
        }
    },
    watch: {
        value(v) {
            this.val = this.initValue(v);
            this.used_days = this.initUsedDays(v);
        }
    }
};
</script>