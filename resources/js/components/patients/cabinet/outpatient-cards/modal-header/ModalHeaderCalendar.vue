<template>
    <div v-if="open" class="header-calendar__wrap">
        <functional-calendar
            ref="calendar"
            v-model="calendarData"
            :configs="calendarConfig"
            @choseDay="selectedChanged"
        />
    </div>
</template>

<script>
import moment from 'moment';

export default {
    props: {
        appointments: Array,
        open: {
            type: Boolean,
            default: false
        }
    },
    data(){
        return {
            calendarData: {},
            calendarConfig: {
                isDatePicker: true,
                changeMonthFunction: true,
                changeYearFunction: true,
                transition: false,
                titlePosition: 'center',
                applyStylesheet: false,
                disabledDates: ['afterToday'],
                dateFormat: 'yyyy-mm-dd',
                dayNames:  [__('Пн'), __('Вт'), __('Ср'), __('Чт'), __('Пт'), __('Сб'), __('Вc')],
                monthNames: [__('Январь,'), __('Февраль,'), __('Март,'), __('Апрель,'), __('Май,'), __('Июнь,'), __('Июль,'), __('Август,'), __('Сентябрь,'), __('Октябрь,'), __('Ноябрь,'), __('Декабрь,')],
                shortMonthNames: [__('Янв'), __('Фев'), __('Мар'), __('Апр'), __('Май'), __('Июн'), __('Июл'), __('Авг'), __('Сент'), __('Окт'), __('Ноя'), __('Дек')],
                markedDates: this.appointments.map((appointment) => moment(appointment.date).format("YYYY-M-D"))
            },
        }
    },
    methods: {
        selectedChanged(data){
            data.formattedDate = moment(data.date).format("YYYY-MM-DD")
            this.$emit('selected', data);

        }
    }
}
</script>
