<template>
    <div class="el-dialog__header-addon">
        <div style="display: flex; margin-right: 15px; position: relative; align-items: center;">
            <span class="no-changes">{{ __('( Изменения по карте:') }} {{ currentDoctorName }} )</span>
            <svg-icon
                name="arrow-left-alt"
                :class="prevBtnClass"
                :title="__('Назад')"
                @click="prev"
            />
            <div class="header-date__wrap" @click="calendarOpen = !calendarOpen">
            <span
                class="header-date"
                v-text="$formatter.dateFormat(currentAppointment.date)"
            ></span>
                <span class="time"><span v-text="formatTime(currentAppointment.start)"></span>{{ __('—') }}<span v-text="formatTime(currentAppointment.end)"></span></span>
            </div>
            <svg-icon
                name="arrow-right-alt"
                :class="nextBtnClass"
                :title="__('Вперёд')"
                @click="next"
            />
            <calendar
                :open="calendarOpen"
                :appointments="appointments"
                @selected="selected" />
        </div>
        <print-button @print="print" />
    </div>
</template>
<script>
import PrintButton from './print/PrintButton.vue';
import Calendar from './ModalHeaderCalendar';

export default {
    components: {
        PrintButton,
        Calendar
    },
    props:{
        appointments: Array,
        specialization: Object
    },
    data() {
        return {
            currentAppointmentIndex: 0,
            currentAppointment: this.appointments[0],
            calendarOpen: false
        }
    },
    computed: {
        currentDoctorName(){
            let fullNameArray = this.currentAppointment.doctor_name.split(' ');

            if(fullNameArray.length === 3){
                let lastName = fullNameArray[0];
                let name = fullNameArray[1].split('')[0];
                let middleName = fullNameArray[2].split('')[0];
                return `${lastName} ${name}. ${middleName}.`
            }
            return this.currentAppointment.doctor_name;
        },
        nextBtnClass: function () {
            let classes = [
                'icon-small',
            ];

            if (this.currentAppointmentIndex === 0) {
                classes.push('icon-grey')
            } else {
                classes.push('icon-light-blue', 'cursor-pointer')
            }
            return classes.join(' ');
        },
        prevBtnClass: function () {
            let classes = [
                'icon-small',
            ];

            if (this.currentAppointmentIndex + 1 === this.appointments.length) {
                classes.push('icon-grey')
            } else {
                classes.push('icon-light-blue', 'cursor-pointer')
            }
            return classes.join(' ');
        }
    },
    methods: {
        selected(date){
            this.$emit('dateSelected', date);
            this.calendarOpen = false;
        },
        print() {
            this.$emit('print');
        },
        next() {
            this.$emit('next');
        },
        prev() {
            this.$emit('prev');
        },
        setData(data) {
            this.currentAppointmentIndex = data.currentAppointmentIndex;
            this.currentAppointment = data.currentAppointment;
        },
        formatTime(time) {
            return this.$moment(time, 'HH:mm:ss').format('HH:mm');
        },
    }
}
</script>
