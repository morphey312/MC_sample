<script>
import DefaultLog from './Default.vue';
import ActionLogRepository from '@/repositories/action-log';
import withClinicRelation from './mixins/withClinicRelation';

export default {
    extends: DefaultLog,
    mixins: [
      withClinicRelation
    ],
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            repository: new ActionLogRepository({filters: {workspace: this.id}}),
            attributes: {
                has_day_sheet: {
                    label: __('Кабинет с табелем рабочего времени'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_active: {
                    label: __('Активный'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_hospital_room: {
                    label: __('Является палатой'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_operational: {
                    label: __('Является операционной'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                specializations: {
                    label: __('Специализации'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },

                name: __('Название'),
                sip_number: __('Номер SIP'),
                appointment_duration: __('Длительность приема'),
                clinic: __('Клиника'),
            },
        };
    },
};
</script>
