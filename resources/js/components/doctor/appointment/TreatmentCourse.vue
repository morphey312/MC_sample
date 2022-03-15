<template>
    <div v-if="course.name" class="mb-40">
        <service-list 
            ref="table"
            :course="course"
            @show-details="showDetails"
            @course-close="courseClose"
            @course-continue="courseContinue" />
    </div>
</template>
<script>
import ServiceList from './treatment-course/List.vue';
import Details from '@/components/patients/cabinet/treatment-courses/Details.vue';
import CompleteModal from './treatment-course/CompleteModal.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        ServiceList,
    },
    props: {
        treatmentCourse: {
            type: Object,
            default: () => ({}),
        },
        patient: {
            type: Object,
            default: () => ({}),
        },
        appointment: {
            type: Object,
            default: () => ({}),
        },
    },
    computed: {
        course() {
            return {
                    start: this.treatmentCourse.start,
                    end: this.treatmentCourse.end,
                    name: this.getCourseName(),
                };
        }
    },
    methods: {
        getCourseName() {
            let name = null;
            if (this.treatmentCourse.appointments) {
                for (let appointment of this.treatmentCourse.appointments) {
                    for (let service of appointment.services) {
                        if (service.is_base === true) {
                            name = service.name;
                            return name;
                        }
                    }
                }
            }
            return name;
        },
        showDetails() {
            this.$modalComponent(Details, {
                course: this.treatmentCourse,
                patient: this.patient,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
                courseChanged: (dialog) => {
                    this.refresh();
                },
            }, {
                header: __('Курс лечения'),
                width: '1030px',
            });
        },
        courseClose() {
            this.$modalComponent(CompleteModal, {
                course: this.treatmentCourse,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                complete: (dialog) => {
                    dialog.close();
                    this.updateCourse(__('Курс лечения успешно завершен'));
                },
            }, {
                header: __('Для завершения курса лечения выберите дату'),
                width: '500px',
            });
        },
        courseContinue() {
            this.$confirm(__('Вы уверены что хотите продлить курс?'), () => {
                this.treatmentCourse.end = null;
                this.updateCourse(__('Курс лечения успешно продлен'));
            });
        },
        updateCourse(message) {
            if (_.isVoid(this.treatmentCourse.doctor_id) && this.appointment.doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE) {
                this.treatmentCourse.doctor_id = this.appointment.doctor_id;
            }
            this.treatmentCourse.save().then(() => {
                this.refresh();       
                this.$info(message);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        refresh() {
            let table = this.$refs.table;
            if (table) {
                table.refresh();
            }
        },
    },
}
</script>