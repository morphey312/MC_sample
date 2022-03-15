<script>
import DefaultLog from './Default.vue';
import ActionLogRepository from '@/repositories/action-log';

export default {
    extends: DefaultLog,
    props: {
        id: {
            type: [String, Number],
            required: true,
        },
    },
    data() {
        return {
            repository: new ActionLogRepository({filters: {patient: this.id}}),
            attributes: {
                firstname: __('Имя'),
                lastname: __('Фамилия'),
                middlename: __('Отчество'),
                passport_no: __('Номер паспорта'),
                gender: {
                    label: __('Пол'),
                    formatter: (val) => {
                        return this.$handbook.getOption('gender', val);
                    }
                },
                status: {
                    label: __('Статус'),
                    formatter: (val) => {
                        return this.$handbook.getOption('patient_status', val);
                    }
                },
                med_insurance: {
                    label: __('Наличие страховки'),
                    formatter: (val) => {
                        return this.$handbook.getOption('med_insurance_availability', val);
                    }
                },
                location: __('Место жительства'),
                house_number: __('Номер дома'),
                apartment_number: __('Номер квартиры'),
                birthday: {
                    label: __('Дата рождения'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                comment: __('Примечание'),
                mailing: {
                    label: __('Подписан на рассылку'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                mailing_analysis: {
                    label: __('Отправлять результаты анализов на e-mail'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                black_mark: {
                    label: __('Черная метка'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                black_mark_reason: {
                    label: __('Причина черной метки'),
                    formatter: (val) => {
                        return this.$handbook.getOption('black_mark_reason', val);
                    }
                },
                black_mark_comment: __('Примечание к черной метке'),
                is_skk: {
                    label: __('Обращение в СКК'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                is_attention: {
                    label: __('Метка «Внимание!»'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                skk_reason: {
                    label: __('Причина обращения в СКК'),
                    formatter: (val) => {
                        return this.$handbook.getOption('skk_reason', val);
                    }
                },
                skk_comment: __('Примечание к обращению в СКК'),
                is_confirmed: {
                    label: __('Личность подтверждена'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                has_registration: {
                    label: __('Зарегистрирован в ЛК'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    },
                },
                source_id: __('Источник информации'),
                clinics: {
                    label: __('Клиника'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                primary_phone_number: {
                    label: __('Номер телефона'),
                    formatter: (val) => {
                        return this.$formatter.phoneNumberFormat(val);
                    }
                },
                primary_phone_clinic: __('Клиника номера телефона'),
                primary_phone_comment: __('Примечание к номеру телефону'),
                secondary_phone_number: {
                    label: __('Дополнительный номер телефона'),
                    formatter: (val) => {
                        return this.$formatter.phoneNumberFormat(val);
                    }
                },
                secondary_phone_clinic: __('Клиника дополнительного номера телефона'),
                secondary_phone_comment: __('Примечание к дополнительному номеру телефону'),
                email: 'Email',
                issued_discount_cards: {
                    label: __('Дисконтные карты'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                relatives: {
                    label: __('Родственники'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                cards: __('Карты'),
                policy: __('Страховой полис'),
                legal_entity_id: __('Корп. клиент'),
            },
        };
    },
};
</script>
