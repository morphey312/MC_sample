<script>
import DefaultLog from '../Default.vue';
import ActionLogRepository from '@/repositories/action-log';
import withClinicRelation from '../mixins/withClinicRelation';

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
            repository: new ActionLogRepository({filters: {specialization: this.id}}),
            attributes: {
                not_show_signal_records: {
                    label: __('Не показывать сигнальные обозначения автоматически'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                not_use_for_new_patient_call: {
                    label: __('Не использовать специализацию для новых звонков пациентов'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                online_appointment: {
                    label: __('Возможность записи в листы из ЛК'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_real_time_appointment: {
                    label: __('Показывать реальное свободное время в ЛК'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                once_in_report: {
                    label: __('Единоразово в отчете'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_check_up: {
                    label: __('Является CHECK UP-ом'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_non_profile_patient: {
                    label: __('Данная специализация является признаком непрофильности пациентов'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_non_treatment: {
                    label: __('По данной специализации не проводится лечение'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                show_days_since_message: {
                    label: __('По данной специализации выводить сообщение о давности посещения клиники пациентом'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                status: {
                    label: __('Статус'),
                    formatter: (val) => {
                        return this.$handbook.getOption('active_status', val);
                    }
                },
                service_group: {
                    label: __('Статус'),
                    formatter: (val) => {
                        return this.$handbook.getOption('service_group', val);
                    }
                },
                additional_templates: {
                    label: __('Доп. шаблоны карты'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                name: __('Название специализации'),
                card_template_id: __('Шаблон амбулаторной карты'),
                order: __('Позиция в листах записи'),
                genitive_name: __('Название в родительном падеже'),
                course_days: __('Количество дней на курс лечения'),
                short_name: __('Сокращенное название специализации'),
                clinic_id: __('Клиника'),
                days_since_last_visit: __('Давность посещения, дни'),
                money_reciever_id: __('Получатель денег'),
                first_patient_appointment_limit: __('Минимальное количество записей'),
                clinic: __('Клиника'),
            },
        };
    },
};
</script>
