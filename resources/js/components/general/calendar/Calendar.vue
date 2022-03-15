<template>
    <div :class="calendarClasses">
        <slot
            :header-props="headerProps"
            name="header">
            <calendar-header
                :header-props="headerProps"
                :period="displayPeriodUom"
                :showPeriodSelect="showPeriodSelect"
                :showToday="showToday"
                @dateChanged="setDate"
                @periodChanged="setPeriod">
                <template slot="header-actions">
                    <slot name="header-actions"/>    
                </template>
            </calendar-header>
        </slot>
        <div class="calendar-view-wrap">
            <div
                v-if="isExtendedMode"
                :style="{backgroundImage: createBackgroundStripes(), backgroundSize: `1px calc(2 * ${lineHeight})`}"
                class="time-scales">
                <div
                    v-for="(time, index) in timeScales"
                    class="time"
                    :key="index"
                    :style="{height: lineHeight}">
                    {{ time }}
                </div>
            </div>
            <div class="calendar-view-inner">
                <div class="cv-header-days">
                    <template v-for="(label, index) in weekdayNames">
                        <slot :index="getColumnDOWClass(index)" :label="label" name="dayHeader">
                            <div
                                :key="getColumnDOWClass(index)"
                                :class="getColumnDOWClass(index)"
                                class="cv-header-day">
                                <template v-if="isExtendedMode">
                                    {{ label }}, {{ getWeekDayDate(index) }}
                                </template>
                                <template v-else>
                                    {{ label }}
                                </template>
                            </div>
                        </slot>
                    </template>
                </div>
                <div
                    :style="isExtendedMode ? {backgroundImage: createBackgroundStripes(), backgroundSize: `1px calc(2 * ${lineHeight})`} : {}"
                    class="cv-weeks">
                    <div
                        v-for="(weekStart, weekIndex) in weeksOfPeriod"
                        :key="`${weekIndex}-week`"
                        :class="getWeekClasses(weekStart, weekIndex)"
                        :style="getWeekStyles()">
                        <div
                            v-for="(day, dayIndex) in daysOfWeek(weekStart)"
                            :key="getColumnDOWClass(dayIndex)"
                            :class="getDayClasses(day, dayIndex)"
                            @click="onClickDay(day)"
                            @drop.prevent="onDrop(day, $event)"
                            @dragover.prevent="onDragOver(day)"
                            @dragenter.prevent="onDragEnter(day, $event)"
                            @dragleave.prevent="onDragLeave(day, $event)">
                            <div
                                v-if="!isExtendedMode"
                                class="cv-day-number">
                                {{ day.getDate() }}
                            </div>
                            <slot :day="day" name="dayContent" />
                        </div>
                        <template v-for="e in getWeekEvents(weekStart)">
                            <div
                                v-if="isEventVisible(e)"
                                :key="e.id"
                                :draggable="enableDragDrop"
                                :class="e.classes"
                                :title="e.title"
                                :style="getEventStyles(e)"
                                class="cv-event"
                                @dragstart="onDragStart(e, $event)"
                                @mouseenter="onMouseEnter(e)"
                                @mouseleave="onMouseLeave"
                                @click.stop="onClickEvent(e)">
                                <slot
                                    :event="e.originalEvent"
                                    :period="displayPeriodUom"
                                    name="event">
                                    <span v-html="getEventTitle(e)" />
                                </slot>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { CalendarView } from 'vue-simple-calendar';
    import CalendarHeader from './Header.vue';

    const  moment = require('moment');

    delete CalendarView.props.events;
    delete CalendarView.props.showDate;
    delete CalendarView.props.displayPeriodUom;
    delete CalendarView.props.periodChangedCallback;
    import translationServer from '@/services/translation';

    export default {
        extends: CalendarView,
        components: {
            CalendarHeader,
        },
        props: {
            initialPeriod: {
                type: String,
                default: 'week',
            },
            dataManager: {
                type: Function,
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
            rowHeight: {
                type: String,
                default: '8em',
            },
            startingDayOfWeek: {
                type: Number,
                default: 1,
            },
            lineHeight: {
                type: String,
                default: '2em',
            },
            extendedTimeStart: {
                type: Date,
                default: () => moment(new Date().setHours(9, 0, 0, 0)).toDate(),
            },
            extendedTimeEnd: {
                type: Date,
                default: () => moment(new Date().setHours(18, 0, 0, 0)).toDate(),
            },
            extendedTimeStep: {
                type: Number,
                default: 5,
            },
            additionalFilter: {
                type: [String, Number],
                default: null
            },
        },
        data() {
            return {
                showDate: new Date(),
                displayPeriodUom: this.initialPeriod,
                events: [],
            };
        },
        mounted() {
            if(this.additionalFilter != null){
                this.$watch('additionalFilter', () => {
                    if (this.additionalFilterChangedCallback) {
                        this.additionalFilterChangedCallback();
                    }
                });
            }
        },
        computed: {
            calendarClasses() {
                return [
                    'cv-wrapper',
                    'locale-' + this.languageCode(this.displayLocale),
                    'locale-' + this.displayLocale,
                    'y' + this.periodStart.getFullYear(),
                    'm' + this.paddedMonth(this.periodStart),
                    'period-' + this.displayPeriodUom,
                    'periodCount-' + this.displayPeriodCount,
                    {
                        past: this.isPastMonth(this.periodStart),
                        future: this.isFutureMonth(this.periodStart),
                        noIntl: !this.supportsIntl,
                    },
                ];
            },
            isExtendedMode() {
                return this.displayPeriodUom == 'week';
            },
            timeScales: {
                cache: true,
                get() {
                    return this.createTimeScales();
                },
            },
            displayLocale() {
                return translationServer.isoLang;
            },
        },
        methods: {
            getWeekClasses(start, index) {
                return [
                    'cv-week',
                    'week' + (index + 1),
                    'ws' + this.isoYearMonthDay(start),
                ];
            },
            getWeekStyles() {
                return {
                    minHeight: this.getRowHeight(),
                };
            },
            getDayClasses(day, index) {
                return [
                    'cv-day',
                    this.getColumnDOWClass(index),
                    'd' + this.isoYearMonthDay(day),
                    'd' + this.isoMonthDay(day),
                    'd' + this.paddedDay(day),
                    'instance' + this.instanceOfMonth(day),
                    {
                        outsideOfMonth: !this.isSameMonth(day, this.defaultedShowDate),
                        today: this.isSameDate(day, this.today()),
                        past: this.isInPast(day),
                        future: this.isInFuture(day),
                        last: this.isLastDayOfMonth(day),
                        lastInstance: this.isLastInstanceOfMonth(day),
                    },
                    ...((this.dateClasses && this.dateClasses[this.isoYearMonthDay(day)]) || []),
                ];
            },
            getEventStyles(e) {
                return {
                    top: this.getEventTop(e),
                    height: this.getEventHeight(e),
                };
            },
            setDate(date) {
                this.showDate = date;
            },
            setPeriod(period) {
                this.displayPeriodUom = period;
            },
            periodChangedCallback(range) {
                this.dataManager(range.displayFirstDate, range.displayLastDate).then((events) => {
                    this.events = events;
                });
            },
            additionalFilterChangedCallback() {
                this.dataManager().then((events) => {
                    this.events = events;
                });
            },
            getWeekDayDate(day) {
                return this.formatWeekDayDate(this.addDays(this.periodStart, day));
            },
            formatWeekDayDate(date) {
                return [
                    this.monthNames[date.getMonth()],
                    date.getDate(),
                ].join(' ');
            },
            getRowHeight() {
                if (this.isExtendedMode) {
                    return `calc(${this.timeScales.length} * ${this.lineHeight})`;
                } else {
                    return this.rowHeight;
                }
            },
            getEventTop(e) {
                if (this.isExtendedMode) {
                    let top = this.extendedTimeStart.getTime();
                    let startTime = Math.max(top, new Date().setHours(e.startDate.getHours(), e.startDate.getMinutes(), 0, 0));
                    let offset = Math.round((startTime - top) / (this.extendedTimeStep * 60000));
                    return `calc(${offset} * ${this.lineHeight})`;
                } else {
                    return `calc(${this.eventTop} + ${e.eventRow} * ${this.lineHeight})`;
                }
            },
            getEventHeight(e) {
                if (this.isExtendedMode && e.endDate) {
                    let top = this.extendedTimeStart.getTime();
                    let bottom = this.extendedTimeEnd.getTime();
                    let startTime = Math.max(top, new Date().setHours(e.startDate.getHours(), e.startDate.getMinutes(), 0, 0));
                    let endTime = Math.min(bottom, new Date().setHours(e.endDate.getHours(), e.endDate.getMinutes(), 0, 0));
                    let height = Math.round((endTime - startTime) / (this.extendedTimeStep * 60000));
                    return `calc(${height} * ${this.lineHeight})`;
                } else {
                    return this.lineHeight;
                }
            },
            createTimeScales() {
                let scales = [];
                let start = moment(this.extendedTimeStart);
                let end = moment(this.extendedTimeEnd);
                while (start.isBefore(end)) {
                    scales.push(start.format('HH:mm'));
                    start.add(this.extendedTimeStep, 'm');
                }
                return scales;
            },
            createBackgroundStripes() {
                return `url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='1'><rect fill='#fafafa' x='0' y='0' width='1' height='50%'/><rect fill='#f0f0f0' x='0' y='50%' width='1' height='50%'/></svg>")`;
            },
            isEventVisible(e) {
                if (this.isExtendedMode) {
                    let bottom = this.extendedTimeEnd.getTime();
                    let startTime = new Date().setHours(e.startDate.getHours(), e.startDate.getMinutes(), 0, 0);
                    if (e.endDate) {
                        let top = this.extendedTimeStart.getTime();
                        let endTime = new Date().setHours(e.endDate.getHours(), e.endDate.getMinutes(), 0, 0);
                        return startTime < bottom && endTime > top;
                    }
                    return startTime < bottom;
                }
                return true;
            },
        },
    }
</script>