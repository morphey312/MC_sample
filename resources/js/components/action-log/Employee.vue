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
            repository: new ActionLogRepository({filters: {employee: this.id}}),
            attributes: {
                phone: __('Номер телефона'),
                full_name: __('ФИО'),
                is_translator: {
                    label: __('Является переводчиком'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                login: __('Логин'),
                roles: {
                    label: __('Группы доступа'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                permissions: {
                    label: __('Дополнительные полномочия'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                password: {
                    label: __('Пароль'),
                    formatter: (val) => {
                        return '******';
                    }
                },
                status: {
                    label: __('Статус'),
                    formatter: (val) => {
                        return this.$handbook.getOption('employee_status', val);
                    }
                },
                position_id: __('Должность'),
                is_primary: {
                    label: __('Основная клиника'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                sip_number: __('Номер SIP'),
                specializations: {
                    label: __('Специализации'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                appointment_duration: __('Длительность первичного приема'),
                appointment_duration_repeated: __('Длительность повторного приема'),
                date_start_working: __('Дата начала работы'),
                date_end_working: __('Дата окончания работы'),
                can_recieve_transfer: __('Может получать межкассовые переводы'),
                sip_password: {
                    label: __('Пароль к телефонии'),
                    formatter: (val) => {
                        return '******';
                    }
                },
                clinic: __('Клиника'),
            },
        };
    },
};
</script>
