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
            repository: new ActionLogRepository({filters: {appointment: this.id}}),
            attributes: {
                date: {
                    label: __('Дата'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                biomaterial_register: {
                    label: __('Регистрация биоматериала'),
                     formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                clinic_id: __('Клиника'),
                start: __('Начало приема'),
                end: __('Окончание приема'),
                is_first: {
                    label: __('Первичный'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                patient_id: __('Пациент'),
                doctor_id: __('Врач/Кабинет'),
                insurance_policy_id: __('Полис'),
                card_specialization_id: __('Специализация карты'),
                operator_id: __('Оператор'),
                specialization_id: __('Специализация'),
                appointment_status_id: __('Статус'),
                status: __('Статус'),
                status_reason_id: __('Причина смены статуса'),
                status_reason_comment: __('Примечание к смене статуса'),
                comment: __('Примечание'),
                source_id: __('Источник информации'),
                is_deleted: {
                    label: __('Удалена'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                appointment_services: {
                    label: __('Услуги/Анализы'),
                    formatter: (val) => {
                        return this.getServiceLog(val);
                    }
                },
                appointment_delete_reason_id: __('Причина удаления'),
                delete_reason_comment: __('Примечание к удалению'),
                discount_card_id: __('Дисконтная карта'),
                treatment_course_id: __('Курс лечения'),
                service: __('Услуга'),
                analysis: __('Анализ'),
                is_base: {
                    label: __('Базовая'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                expected_payment: __('Ожидаемая оплата'),
                by_policy: {
                    label: __('По полису'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                franchise: __('Франшиза'),
                warranter: __('Гарант'),
                not_debt: {
                    label: __('Не должник'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                do_not_take_payment: {
                    label: __('Оплату не брать'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                price_id: __('Тариф'),
                legal_entity_id: __('Корп. клиент'),
                surgery_employees: {
                    label: __('Операционные врачи'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                },
                printed: {
                    label: __('Распечатано'),
                    formatter: (val) => {
                        return this.$formatter.boolToString(val);
                    }
                },
                downloaded: {
                    label: __('Загружено'),
                    formatter: (val) => {
                        return this.$formatter.boolToString(val);
                    }
                },
                quantity: __('Количество'),
                cost: {
                    label: __('Стоимость'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val);
                    }
                },
                discount: {
                    label: __('Скидка'),
                    formatter: (val) => {
                        return this.$formatter.numberFormat(val, 0);
                    }
                },
                new_assigner: __('Назначил'),
                status: {
                    label: __('Статус'),
                    formatter: (val) => {
                        return this.$handbook.getOption('analysis_status', val);
                    }
                },
                date_expected_pass: {
                    label: __('Предполагаемая дата сдачи'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                date_pass: {
                    label: __('Дата сдачи'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                date_expected_ready: {
                    label: __('Предполагаемая дата готовности'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                date_ready: {
                    label: __('Дата готовности'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                date_sent_email: {
                    label: __('Дата отправки на email'),
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                delivery_status: {
                    label: __('Статус доставки'),
                    formatter: (val) => {
                        return this.$handbook.getOption('delivery_status', val);
                    }
                },
                attachments: {
                    label: __('Вложение'),
                    formatter: (val) => {
                        return this.$formatter.listFormat(val);
                    }
                }
            },
        };
    },
    methods: {
        getChanges(row) {
            let service = row.new.new_service;
            let oldService = row.old.old_service;
            let analysis = row.new.new_analysis;
            let oldAnalysis = row.old.old_analysis;
            delete row.new.new_service;
            delete row.new.new_analysis;
            delete row.old.old_analysis;
            delete row.old.old_assigner;
            delete row.old.old_service;

            let result = DefaultLog.methods.getChanges.call(this, row);

            if (service !== undefined) {
                result = result.map((item) => {
                    item.label = `${item.label}, ${service}`;
                    return item;
                });
            }

            if (analysis !== undefined) {
                result = result.map((item) => {
                    item.label = `${item.label}, ${analysis}`;
                    return item;
                });
            }

            if (service !== oldService) {
                result.unshift({
                    label: this.fieldLabel('service'),
                    old: this.formatFieldValue('service', oldService),
                    new: this.formatFieldValue('service', service),
                });
            }

            if (analysis !== oldAnalysis) {
                result.unshift({
                    label: this.fieldLabel('analysis'),
                    old: this.formatFieldValue('analysis', oldAnalysis),
                    new: this.formatFieldValue('analysis', analysis),
                });
            }

            return result;
        },
        getServiceLog(value) {
            let services = value.map(row => {
                return this.parseService(row);
            });
            return services.join('<br>');
        },
        parseService(service) {
            const serviceDivider = ";;"
            const itemsDivider = ";$"
            let data = service.split(serviceDivider);
            return '<b>' + data[0] + __('</b>: количество -') + ' ' + data[1] + __(', стоимость -') + ' ' + data[2] + __(', базовая -') + ' ' +
                    this.$formatter.boolToString(Number(data[3])) +
                    __(', скидка -') + ' ' + data[4] +
                    (_.isFilled(data[5]) ? (': ' + data[5].replace(itemsDivider, ',')) : '') +
                    __(', по страховой:') + ' ' + this.$formatter.boolToString(Number(data[6])) + (data[7] ? __(', франшиза:') + ' ' + Number(data[7]) : '')+
                    (data[8] ? __(', гарант:') + ' ' + data[8] : '');
        },
    },
};
</script>
