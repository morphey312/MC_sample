<template>
    <div>
        <div class="sections-wrapper">
            <functional-calendar
                ref="calendar"
                v-model="calendarData"
                :configs="calendarConfig"
                @choseDay="selectedChanged"
            />
        </div>
        <div class="form-footer text-right mp-0">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="disabled"
                @click="selected">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import CallRequest from '@/models/call-request'
import CallRequestPurposeRepository from '@/repositories/call-request/purpose';
import NextVisit from "@/models/patient/card/next-visit";

export default {
    props: {
        appointment: Object,
        nextVisit: Object
    },
    data() {
        let limits = {
            min: this.$moment().format('D/M/YYYY'),
            max: this.$moment().add(3, 'years').format('D/M/YYYY'),
        };

        let nextVisitModel = this.nextVisit ? this.nextVisit : new NextVisit({
            card_specialization_id: this.appointment.specialization_card.id,
            appointment_id: this.appointment.id
        });

        return {
            calendarData: {},
            model: new CallRequest({
                id: nextVisitModel.call_request_id,
                clinic_id: this.appointment.clinic_id,
                call_request_purpose_id: null,
                specialization_id: this.appointment.specialization_id,
                doctor_id: this.appointment.doctor_id,
                doctor_type: this.appointment.doctor_type,
                patient_id: this.appointment.patient_id,
            }),
            nextVisitModel: nextVisitModel,
            calendarConfig: {
                isDatePicker: true,
                changeMonthFunction: true,
                changeYearFunction: true,
                transition: false,
                applyStylesheet: false,
                dateFormat: 'yyyy-mm-dd',
                limits,
                dayNames:  [__('Пн'), __('Вт'), __('Ср'), __('Чт'), __('Пт'), __('Сб'), __('Вc')],
                monthNames: [__('Январь,'), __('Февраль,'), __('Март,'), __('Апрель,'), __('Май,'), __('Июнь,'), __('Июль,'), __('Август,'), __('Сентябрь,'), __('Октябрь,'), __('Ноябрь,'), __('Декабрь,')],
                shortMonthNames: [__('Янв'), __('Фев'), __('Мар'), __('Апр'), __('Май'), __('Июн'), __('Июл'), __('Авг'), __('Сент'), __('Окт'), __('Ноя'), __('Дек')]
            },
        }
    },
    computed: {
        disabled() {
            return _.isVoid(this.model.recall_to) || _.isVoid(this.model.recall_from);
        },
    },
    mounted() {
        this.getCallRequests();
    },
    methods: {
        cancel() {
            this.nextVisitModel.reset();
            this.$emit('cancel')
        },
        selected() {
            this.model.save().then(() => {
                this.nextVisitModel.call_request_id = this.model.id;
                this.nextVisitModel.save().then(() => {
                    this.$info(__('Дата визита успешно назначена'));
                    this.$emit('saved', this.nextVisitModel);
                });
            }).catch((error) => {
                this.$displayErrors({error});
                this.$error(__('Не удалось назначить визит'));
            })
        },
        selectedChanged() {
            this.setNextVisit();
            let today = this.$moment();
            let recallTo = this.$moment(this.calendarData.selectedDate).subtract(1, 'days');
            if (recallTo.isSameOrAfter(this.$moment().subtract(1, 'days'), 'day')) {
                if (recallTo.isAfter(today, 'day')) {
                    this.setSelected(today, recallTo)
                } else {
                    this.$confirm(__('Внимание! Пациенту рекомендуется записаться на ресепшине'), () => {
                        this.setSelected(today, recallTo)
                    });
                }
            } else {
                delete this.calendarData.selectedDate;
                this.model.recall_to = null;
                this.model.recall_from = null;
                this.$warning(__('Пациенту необходимо записаться на ресепшине'));
            }
        },
        setSelected(today, recallTo) {
            this.model.recall_to = this.formatDate(recallTo);
            let dayDifference = recallTo.diff(today, 'days');

            if (dayDifference == 1) {
                this.model.recall_from = this.formatDate(recallTo);
            } else if (dayDifference > 3) {
                this.model.recall_from = this.formatDate(recallTo.subtract(3, 'days'));
            } else {
                this.model.recall_from = this.formatDate(recallTo.subtract(dayDifference, 'days'));
            }

            this.model.recommended_appointment_date = this.formatDate(this.$moment(this.calendarData.selectedDate));
            this.model.comment = this.$formatter.dateFormat(this.calendarData.selectedDate) + ', ' + this.appointment.doctor.name
        },
        setNextVisit(){
            this.nextVisitModel.next_visit_date = this.calendarData.selectedDate;
        },
        formatDate(date, format = 'YYYY-MM-DD') {
            return this.$formatter.dateFormat(date, format);
        },
        getCallRequests() {
            let purpose = new CallRequestPurposeRepository();
            purpose.fetch({next_visit: 1}).then((response) => {
                if (response.rows.length !== 0) {
                    this.model.call_request_purpose_id = response.rows[0].id;
                }
            });
        },
    },
}
</script>
