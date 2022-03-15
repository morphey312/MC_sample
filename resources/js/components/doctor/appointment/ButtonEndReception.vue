<template>
    <div class="button-end-reception text-right card-record-section">
        <el-button
            type="primary"
            @click="clickEnd"
        >
            {{ __('Завершить прием пациента') }}
        </el-button>
    </div>
</template>
<script>
import RejectionReasonModal from './RejectionReasonModal.vue';
import CompleteCourseModal from './treatment-course/CompleteModal.vue';
import CONSTANTS from '@/constants';
import Promise from "lodash/_Promise";

export default {
    props: {
        previousVisit: Object,
        appointment: Object,
        treatmentAssignment: Object,
        treatmentCourse: Object,
        outpatientRecord: Object,
        cardAssignments: Array,
    },
    computed: {
        hasAptekaTwentyFourMedicines() {
            return this.cardAssignments.find((assignment) => {
                if (assignment.recordable.assigned_medicines) {
                    return assignment.recordable.assigned_medicines.find((medicine) => {
                        return medicine.is_apteka24 && medicine.apteka24_order_id === null;
                    })
                }
            })
        },
    },
    methods: {
        clickEnd() {
            new Promise((resolve, reject) => {
                if (this.hasAptekaTwentyFourMedicines) {
                    this.$confirm(__('В назначенных медикаментах присутствуют медикаменты из сервиса Apteka24 вы подтверждаете оформление заказа ?'), () => {
                        this.createAptekaTwentyFourOrder().then((response) => {
                            if (response.data.status === 'success') {
                                this.$info(__('Заказ на apteka24 успешно создан'), {customClass: 'always-on-front'});
                                this.setApteka24OrderIdToAssignedMedicines(response.data.order_id);
                                resolve(response);
                            }
                        }).catch((error) => {
                            if (error.response.data.status === 'disabled' || error.response.status === 501) {
                                this.$info(__('В заказе были медикаменты из Apteka24 но она была выключена конфигурацией'), {customClass: 'always-on-front'});
                            }
                            reject(error);
                        });
                    }, {
                        cancelled: () => {
                            resolve();
                        }
                    })
                } else {
                    resolve();
                }
            }).finally(() => {
                if (this.previousVisit == null && this.appointment.treatment_course_id == null) {
                    return this.callRejectionForm();
                } else {
                    if (this.appointment.treatment_course_id != null && _.isVoid(this.treatmentCourse.end)) {
                        return this.$confirm(__('Курс лечения не закрыт. Хотите поставить дату окончания курса?'), () => {
                            return this.finishCourse();
                        }, {
                            cancelled: () => {
                                return this.finishAppointment();
                            },
                            cancelBtnText: __('Завершить прием пациента'),
                            confirmBtnText: __('Поставить дату окончания курса'),
                        });
                    }
                    return this.finishAppointment();
                }
            });
        },
        setApteka24OrderIdToAssignedMedicines(orderId) {
            return this.cardAssignments.forEach((assignment) => {
                if (assignment.recordable.assigned_medicines) {
                    assignment.recordable.assigned_medicines.forEach((medicine) => {
                        if (medicine.is_apteka24 && medicine.apteka24_order_id === null) {
                            medicine.apteka24_order_id = orderId;
                        }

                    })
                }
            })
        },
        createAptekaTwentyFourOrder() {
            return axios.post('/api/v1/apteka24/order/create',
                {
                    patient_name: this.appointment.patient.firstname,
                    patient_phone: this.appointment.patient.contact_details.primary_phone_number,
                    medicines: this.cardAssignments
                });
        },
        callRejectionForm() {
            this.$modalComponent(RejectionReasonModal, {
                model: this.appointment
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Укажите причину не взятия лечения'),
                width: '450px',
                customClass: 'no-footer',
            });
        },
        finishCourse() {
            this.$modalComponent(CompleteCourseModal, {
                course: this.treatmentCourse,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                complete: (dialog) => {
                    dialog.close();
                    if (_.isVoid(this.treatmentCourse.doctor_id) && this.appointment.doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE) {
                        this.treatmentCourse.doctor_id = this.appointment.doctor_id;
                    }

                    this.treatmentCourse.save().then(() => {
                        this.$info(__('Курс лечения успешно завершен'));
                        return this.finishAppointment();
                    });
                },
            }, {
                header: __('Для завершения курса лечения выберите дату'),
                width: '500px',
            });
        },
        finishAppointment() {
            if (_.isVoid(this.treatmentAssignment) || this.isFilledDiagnoses()) {


                return this.routeToSchedule();
            } else {
                this.$eventHub.$emit('validationErrors', {
                    diagnosis_icd: [''],
                });
                return this.$error(__('Укажите и сохраните диагноз по МКБ в курсе лечения'));
            }
        },
        isFilledDiagnoses() {
            return _.isFilled(this.outpatientRecord) && this.outpatientRecord.diagnosis_icd.length != 0;
        },
        routeToSchedule() {
            if (this.appointment) {
                this.appointment.changeStatus({system_status: CONSTANTS.APPOINTMENT.STATUSES.COMPLETED}).then(() => {
                    this.$router.push({name: 'doctor-schedule'});
                });
            }
        },
    },
};
</script>
