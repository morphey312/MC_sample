<template>
    <el-menu
        :collapse="isCollapse"
        class="side-menu">
        <menu-item
            v-for="(item, index) in items"
            :item="item"
            :num="index"
            :key="index" />
        <el-menu-item
            key="logout"
            index="logout">
            <a href="#" @click.prevent="logoutConfirmation">
                <svg-icon name="logout" class="icon-white" />
                <span class="logout">{{ __('Выйти') }}</span>
            </a>
        </el-menu-item>
    </el-menu>
</template>

<script>
import {getCloseWarning, makeUnsafeAll} from '@/services/safe-close';
import MenuMixin from '@/mixins/menu';
import lts from '@/services/lts';

export default {
    mixins: [
        MenuMixin,
    ],
    props: {
        isCollapse: Boolean,
    },
    data() {
        return {
            items: this.accessFilter([
                {
                    icon: 'menu-reports',
                    title: __('Записи на прием'),
                    children: [
                        {
                            title: __('Листы записей к врачам'),
                            permission: [
                                'appointments-sheets.access',
                                'appointments-sheets.access-clinic',
                            ],
                            route: {
                                name: 'appointment-schedule'
                            },
                        },
                        {
                            title: __('Фильтр записей пациентов'),
                            permission: [
                                'appointments.access',
                                'appointments.access-clinic',
                            ],
                            route: {
                                name: 'appointments'
                            },
                        },
                      /*   {
                            title: __('Архив блокировок времени'),
                            permission: [
                                'lock-log.access',
                                'lock-log.access-clinic',
                            ],
                            route: {
                                name: 'lock-log'
                            },
                        }, */
                        {
                            title: __('Банк анализов'),
                            permission: [
                                'analysis-results.access',
                                'analysis-results.access-clinic',
                            ],
                            route: {
                                name: 'analysis-results'
                            },
                        },
                        {
                            title: __('Журнал причин задержки изменения статуса'),
                            permission: [
                                'appointment-delays.access',
                                'appointment-delays.access-clinic',
                            ],
                            route: {
                                name: 'appointment-delays-journal'
                            },
                        },
                        {
                            title: __('Журнал стационарный'),
                            permission: [
                                'appointments.stationare-journal',
                            ],
                            route: {
                                name: 'stationare-patient-journal'
                            },
                        },
                        {
                            title: __('Журнал оперативных вмешательств'),
                            permission: [
                                'appointments.surgery-journal',
                            ],
                            route: {
                                name: 'stationare-surgery-journal'
                            },
                        },
                        {
                            title: __('Журнал вызовов скорой помощи'),
                            permission: [
                                'ambulance-calls.access',
                            ],
                            route: {
                                name: 'ambulance-call'
                            },
                        },
                        {
                            title: __('Журнал вызовов скорой помощи'),
                            permission: [
                                'ambulance-calls.access',
                            ],
                            route: {
                                name: 'ambulance-call'
                            },
                        },
                        {
                            title: __('Видеоконсультации'),
                            permission: [
                                'video-chat.access',
                            ],
                            route: {
                                name: 'videoconsultations-log'
                            },
                        },
                    ],
                },
                {
                    icon: 'call-incoming',
                    title: __('Контактный центр'),
                    children: [
                        {
                            title: __('Call-центр'),
                            permission: [
                                'call-center.access',
                            ],
                            route: {
                                name: 'voip'
                            },
                        },
                        {
                            title: __('Информационные звонки'),
                            permission: [
                                'calls.access',
                                'calls.access-clinic',
                            ],
                            route: {
                                name: 'calls'
                            },
                        },
                        {
                            title: __('Причины невозможности обработки звонка'),
                            permission: 'call-unprocessibility-reasons.access',
                            route: {
                                name: 'reason-impossibility-call-processing'
                            },
                        },
                        {
                            title: __('Причины удаления информационного звонка'),
                            permission: 'call-delete-reasons.access',
                            route: {
                                name: 'call-delete-reasons'
                            },
                        },
                        {
                            title: __('Причины отмены заявки в листе ожидания'),
                            permission: 'wait-list-record-cancel-reasons.access',
                            route: {
                                name: 'wait-list-record-cancel-reasons'
                            },
                        },
                        {
                            title: __('Результаты звонков'),
                            permission: 'call-results.access',
                            route: {
                                name: 'call-results'
                            },
                        },
                        {
                            title: __('Цели прозвона'),
                            permission: 'call-request-purposes.access',
                            route: {
                                name: 'call-request-purposes'
                            },
                        },
                        {
                            title: __('Бонусы операторов'),
                            children: [
                                {
                                    title: __('Нормы клиник'),
                                    permission: 'operator-bonuses.access',
                                    route: {
                                        name: 'clinic-bonus-norms'
                                    },
                                },
                                {
                                    title: __('Показатели операторов'),
                                    permission: 'operator-bonuses.access',
                                    route: {
                                        name: 'operator-bonus-norms'
                                    },
                                }
                            ]
                        },
                        {
                            title: __('Настройки уведомлений'),
                            permission: [
                                'notification-channels.access',
                                'notification-templates.access',
                                'notification-mailing-providers.access',
                                'notification-mailing-templates.access',
                            ],
                            route: {
                                name: 'notification-settings'
                            },
                        },
                        {
                            title: __('База ожидания'),
                            route: {
                                name: 'wait-list-records'
                            },
                            visible: this.$can(['wait-list-record.access', 'wait-list-record.access-clinic'], true)
                                && !this.$can('call-center.access'),
                        },
                    ]
                },
                {
                    icon: 'menu-patients',
                    title: __('Пациенты'),
                    children: [
                        {
                            title: __('Пациенты - фильтр поиска'),
                            permission: [
                                'patients.access',
                                'patients.access-clinic',
                            ],
                            route: {
                                name: 'patients'
                            },
                        },
                        {
                            title: __('Записи пациентов'),
                            children: [
                                {
                                    title: __('Правила ограничения записи пациентов'),
                                    permission: [
                                        'limitations.access',
                                        'limitations.access-clinic',
                                    ],
                                    route: {
                                        name: 'appointment-limitations'
                                    },
                                },
                                {
                                    title: __('Причины удаления записи пациентов'),
                                    permission: 'appointment-delete-reasons.access',
                                    route: {
                                        name: 'appointment-delete-reasons'
                                    },
                                },
                                {
                                    title: __('Статусы'),
                                    permission: [
                                        'appointment-statuses.access',
                                        'appointment-status-reasons.access',
                                    ],
                                    route: {
                                        name: 'appointment-statuses'
                                    },
                                },
                            ]
                        },
                        {
                            title: __('Дисконтные карты'),
                            children: [
                                {
                                    title: __('Виды нумерации дисконтных карт'),
                                    permission: [
                                        'card-numbering-kinds.access',
                                        'card-numbering-kinds.access-clinic',
                                    ],
                                    route: {
                                        name: 'card-numbering-kinds'
                                    },
                                },
                                {
                                    title: __('Типы дисконтных карт'),
                                    permission: [
                                        'discount-card-types.access',
                                        'discount-card-types.access-clinic',
                                    ],
                                    route: {
                                        name: 'discount-card-types'
                                    },
                                },
                                {
                                    title: __('Иконки дисконтных карт'),
                                    permission: 'discount-card-icons.access',
                                    route: {
                                        name: 'card-type-icons'
                                    },
                                },
                            ]
                        },
                        {
                            title: __('Поиск дубликатов'),
                            permission: 'patients.merge',
                            route: {
                                name: 'patients-duplicates'
                            },
                        },
                        {
                            title: __('Регистрация ЛК'),
                            permission: [
                                'patient-registrations.access',
                                'patient-users.access'
                            ],
                            route: {
                                name: 'patients-registrations'
                            },
                        },
                        {
                            title: __('Причина не взятия лечения'),
                            permission: 'reason-refusing-treatments.access',
                            route: {
                                name: 'reason-refusing-treatments'
                            },
                        }
                    ],
                },
                {
                    icon: 'menu-clinics',
                    title: __('Клиники'),
                    children: [
                        // TODO::OCMC-940 unfinished functionality
                        // {
                        //     title: __('Стационар'),
                        //     permission: 'departments.access-ocсupation',
                        //     route: {
                        //         name: 'occupations'
                        //     },
                        // },
                        {
                            title: __('Валюта'),
                            permission: 'currencies.access',
                            route: {
                                name: 'currencies'
                            },
                        },
                        {
                            title: __('Страны'),
                            permission: 'countries.access',
                            route: {
                                name: 'countries'
                            },
                        },
                        {
                            title: __('Причины блокировки времени'),
                            permission: 'day-sheet-time-block-reasons.access',
                            route: {
                                name: 'day-sheet-time-block-reason'
                            },
                        },
                        {
                            title: __('Города'),
                            permission: 'cities.access',
                            route: {
                                name: 'cities'
                            },
                        },
                        {
                            title: __('Предоставители мед. услуг'),
                            permission: 'msp.access',
                            route: {
                                name: 'msp'
                            },
                        },
                        {
                            title: __('Клиники'),
                            permission: 'clinics.access',
                            route: {
                                name: 'clinics'
                            },
                        },
                        {
                            title: __('Специализации'),
                            permission: [
                                'specializations.access',
                                'specializations.access-clinic',
                            ],
                            route: {
                                name: 'specializations'
                            },
                        },
                        {
                            title: __('Виды оплат'),
                            permission: 'payment-methods.access',
                            route: {
                                name: 'payment-methods'
                            },
                        },
                        {
                            title: __('Назначение платежа'),
                            permission: 'service-payment-destinations.access',
                            route: {
                                name: 'service-payment-destinations'
                            },
                        },
                        {
                            title: __('Справочник медикаментов'),
                            permission: 'medicines.access',
                            route: {
                                name: 'price-list-medicines'
                            },
                        },
                        {
                            title: __('Диагнозы'),
                            permission: 'diagnoses.access',
                            route: {
                                name: 'diagnoses'
                            },
                        },
                        {
                            title: __('Бланки документов'),
                            permission: [
                                'patient-documents.access',
                                'patient-documents.access-clinic',
                                'protocol-templates.access',
                                'protocol-templates.access-clinic',
                            ],
                            route: {
                                name: 'blanks'
                            },
                        },
                        {
                            title: __('Прогноз прихода по врачам'),
                            permission: [
                                'doctor-income-plans.access',
                                'doctor-income-plans.access-clinic'
                            ],
                            route: {
                                name: 'employee-doctor-income-plans'
                            },
                        },
                    ],
                },
                {
                    icon: 'menu-diagnostics',
                    title: __('Лаборатории'),
                    children: [
                        {
                            title: __('Список'),
                            permission: [
                                'laboratories.access',
                                'laboratories.access-clinic',
                            ],
                            route: {
                                name: 'laboratories'
                            },
                        },
                        {
                            title: __('Расписание работы'),
                            permission: 'laboratories-schedule.access',
                            route: {
                                name: 'laboratories-schedule'
                            },
                        },
                        {
                            title: __('Анализы из MC+Lab'),
                            permission: 'analyses-candidate.access',
                            route: {
                                name: 'analyses-candidate'
                            },
                        },
                        {
                            title: __('Заказ-наряды в лабораторию'),
                            permission: [
                                'laboratory-orders.access',
                                'laboratory-orders.clinic-access',
                                'laboratory-transfers.access',
                                'laboratory-transfers.clinic-access',
                            ],
                            route: {
                                name: 'laboratory-orders'
                            },
                        },
                    ],
                },
                {
                    icon: 'conference',
                    title: __('Отдел кадров'),
                    children: [
                        {
                            title: __('Группы доступов'),
                            permission: 'roles.access',
                            route: {
                                name: 'roles'
                            },
                        },
                        {
                            title: __('Должности'),
                            permission: [
                                'positions.access',
                                'positions.access-clinic',
                            ],
                            route: {
                                name: 'positions'
                            },
                        },
                        {
                            title: __('Сотрудники'),
                            permission: [
                                'employees.access',
                                'employees.access-clinic',
                            ],
                            route: {
                                name: 'employees'
                            },
                        },
                        {
                            title: __('Кабинеты'),
                            permission: [
                                'workspaces.access',
                                'workspaces.access-clinic',
                            ],
                            route: {
                                name: 'workspaces'
                            },
                        },
                        {
                            title: __('Табеля'),
                            permission: [
                                'day-sheets.access',
                                'day-sheets.access-clinic',
                            ],
                            route: {
                                name: 'day-sheets'
                            },
                        },
                    ],
                },
                {
                    icon: 'report',
                    title: __('Страховые компании'),
                    children: [
                        {
                            title: __('Справочник страховых компаний'),
                            permission: [
                                'insurance.access',
                                'insurance.access-clinic',
                            ],
                            route: {
                                name: 'insurance-companies'
                            },
                        },
                        {
                            title: __('Прайсы'),
                            children: [
                                {
                                    title: __('Услуги'),
                                    permission: [
                                        'insurance-prices.access',
                                    ],
                                    route: {
                                        name: 'insurance-services-prices'
                                    },
                                },
                                {
                                    title: __('Загрузка прайса для услуг'),
                                    permission: [
                                        'insurance-prices.upload'
                                    ],
                                    route: {
                                        name: 'insurance-services-prices-upload',
                                    },
                                },
                                {
                                    title: __('Анализы'),
                                    permission: [
                                        'insurance-prices.access',
                                    ],
                                    route: {
                                        name: 'insurance-analyses-prices',
                                    },
                                },
                                {
                                    title: __('Загрузка прайса для анализов'),
                                    permission: [
                                        'insurance-prices.upload',
                                    ],
                                    route: {
                                        name: 'insurance-analyses-prices-upload',
                                    },
                                },
                            ]
                        },
                        {
                            title: __('Акты'),
                            permission: [
                                'insurance-acts.access',
                                'insurance-acts.access-clinic',
                            ],
                            route: {
                                name: 'insurance-execution-acts'
                            },
                        },
                    ],
                },
                {
                    icon: 'patient-contacts',
                    title: __('Корпоративные клиенты'),
                    permission: [
                        'legal-entities.access',
                        'legal-entities.access-clinic',
                    ],
                    route: {
                        name: 'legal-entities'
                    },
                },
                {
                    icon: 'catalogue',
                    title: __('Медицинские услуги'),
                    children: [
                        {
                            title: __('Услуги'),
                            permission: [
                                'services.access',
                                'services.access-clinic',
                            ],
                            route: {
                                name: 'services'
                            },
                        },
                        {
                            title: __('Анализы'),
                            permission: [
                                'analyses.access',
                                'analyses.access-clinic',
                            ],
                            route: {
                                name: 'analyses'
                            },
                        },
                        {
                            title: __('Поиск дубликатов'),
                            children: [
                                {
                                    title: __('Услуги'),
                                    permission: 'services.merge',
                                    route: {
                                        name: 'services-duplicates'
                                    },
                                },
                                {
                                    title: __('Анализы'),
                                    permission: 'analyses.merge',
                                    route: {
                                        name: 'analyses-duplicates'
                                    },
                                },
                            ],
                        },
                        {
                            title: __('Прайсы'),
                            children: [
                                {
                                    title: __('Услуги'),
                                    permission: [
                                        'service-prices.access',
                                        'service-prices.access-clinic',
                                    ],
                                    route: {
                                        name: 'price-list-services',
                                    },
                                },
                                {
                                    title: __('Загрузка прайса для услуг'),
                                    permission: [
                                        'service-prices.upload',
                                    ],
                                    route: {
                                        name: 'price-list-services-upload',
                                    },
                                },
                                {
                                    title: __('Анализы'),
                                    permission: [
                                        'analysis-prices.access',
                                        'analysis-prices.access-clinic',
                                    ],
                                    route: {
                                        name: 'price-list-analyses',
                                    },
                                },
                                {
                                    title: __('Загрузка прайса для анализов'),
                                    permission: [
                                        'analysis-prices.upload',
                                    ],
                                    route: {
                                        name: 'price-list-analyses-upload',
                                    },
                                },
                            ],
                        },
                        {
                            title: __('Согласование прайсов'),
                            permission: [
                                'price-agreement-acts.access',
                                'price-agreement-acts.access-clinic',
                            ],
                            route: {
                                name: 'agreement-of-prices'
                            },
                        },
                    ]
                },
                {
                    icon: 'arhive',
                    title: __('Отчеты'),
                    children: [
                        {
                            title: __('Контактный центр'),
                            permission: [
                                'call-center-reports.access',
                                'call-center-reports.access-clinic',
                            ],
                            route: {
                                name: 'call-center-report'
                            },
                        },
                        {
                            title: __('Регистратура'),
                            permission: [
                                'registry-reports.access',
                                'registry-reports.access-clinic',
                            ],
                            route: {
                                name: 'registry-report'
                            },
                        },
                        {
                            title: __('Финансовый отдел'),
                            permission: [
                                'finance-reports.access',
                                'finance-reports.access-clinic',
                            ],
                            route: {
                                name: 'finance-report'
                            },
                        },
                        {
                            title: __('Медикаменты'),
                            permission: 'finance-reports.issued-medicines',
                            route: {
                                name: 'patients-issued-medicines'
                            },
                        },
                        {
                            title: __('Отдел Маркетинга'),
                            permission: [
                                'marketing-reports.access',
                            ],
                            route: {
                                name: 'marketing-report'
                            },
                        },
                        {
                            title: __('Инкам'),
                            permission: [
                                'finance-reports.income',
                            ],
                            route: {
                                name: 'income-report',
                            }
                        },
                        {
                            title: __('Оборот по специализации'),
                            permission: [
                                'finance-reports.doctor-specialization',
                                'finance-reports.doctor-specialization-clinic',
                            ],
                            route: {
                                name: 'doctor-specialization',
                            }
                        },
                        {
                            title: __('Причины не взятия лечения'),
                            permission: [
                                'treatment-refusings.access',
                                'treatment-refusings.access-clinic',
                            ],
                            route: {
                                name: 'treatment-refusings',
                            }
                        },
                        {
                            title: __('Заявки eHealth'),
                            permission: [
                                'ehealth.application-history',
                            ],
                            route: {
                                name: 'ehealth-application-history',
                            }
                        },
                        {
                            title: __('Интерактивные'),
                            children: [
                                {
                                    title: __('ИнкамV2'),
                                    permission: [
                                        'finance-reports.income-v2',
                                    ],
                                    route: {
                                        name: 'income-report-v2',
                                    }
                                },
                                {
                                    title: __('ПернаправленияV2'),
                                    permission: [
                                        'finance-reports.redirects-v2',
                                    ],
                                    route: {
                                        name: 'redirects-report-v2',
                                    }
                                },
                                {
                                    title: __('ПернаправленияV3'),
                                    permission: [
                                        'finance-reports.redirects-v2',
                                    ],
                                    route: {
                                        name: 'redirects-report-v3',
                                    }
                                },
                                {
                                    title: __('Планирование'),
                                    permission: [
                                        'finance-reports.doctor-income-plan',
                                    ],
                                    route: {
                                        name: 'doctor-income-plan-report',
                                    }
                                },
                                {
                                    title: __('Оборот по специализации')+' V2',
                                    permission: [
                                        'finance-reports.doctor-income-plan',
                                    ],
                                    route: {
                                        name: 'doctor-specialization-v2',
                                    }
                                },
                                {
                                    title: __('Проданные услуги')+' V2',
                                    permission: [
                                        'finance-reports.access',
                                        'finance-reports.access-clinic',
                                    ],
                                    route: {
                                        name: 'sold-services-interactive'
                                    },
                                },
                                {
                                    title: __('Маркетинг'),
                                    permission: [
                                        'marketing-reports.access'
                                    ],
                                    children: [
                                        {
                                            title: __('Сводная итого'),
                                            route: {
                                                name: 'marketing-report-v2',
                                            },
                                            permission: [
                                                'marketing-reports.totals'
                                            ],
                                        },
                                        {
                                            title: __('Города'),
                                            route: {
                                                name: 'marketing-report-cities-v2',
                                            },
                                            permission: [
                                                'marketing-reports.cities'
                                            ],
                                        },
                                    ]
                                },
                                {
                                    title: __('Call Center'),
                                    permission: [
                                        'call-center-reports.access',
                                        'call-center-reports.access-clinic',
                                    ],
                                    children: [
                                        {
                                            title: __('Звонки/Записи/Приходы'),
                                            route: {
                                                name: 'calls-income-interactive',
                                            }
                                        },
                                        {
                                            title: __('Звонки/Записи/Приходы')+' V2',
                                            route: {
                                                name: 'calls-income-interactive-v2',
                                            }
                                        },
                                        {
                                            title: __('Бонусы операторов'),
                                            route: {
                                                name: 'operator-bonuses-interactive',
                                            }
                                        },
                                        {
                                            title: __('Отчет по пропущенным звонкам'),
                                            route: {
                                                name: 'calls-missed-interactive',
                                            }
                                        },
                                    ]
                                },
                            ]
                        }
                    ],
                },
                {
                    icon: 'menu-cashbox',
                    title: __('Финансы'),
                    children: [
                        {
                            title: __('Касса'),
                            permission: [
                                'payments.create',
                                'payments.create-clinic',
                            ],
                            route: {
                                name: 'cashier'
                            },
                        },
                        {
                            title: __('Приход'),
                            permission: [
                                'payments.access',
                                'payments.access-clinic',
                            ],
                            route: {
                                name: 'list-cashier'
                            },
                        },
                        {
                            title: __('Онлайн оплата. Возвраты'),
                            permission: [
                                'payments.online-refund',
                            ],
                            route: {
                                name: 'refund-online-list'
                            },
                        },
                        {
                            title: __('Курс валют'),
                            permission: [
                                'payments.access-exchange',
                            ],
                            route: {
                                name: 'exchange-list'
                            },
                        },
                        {
                            title: __('Форма оплаты'),
                        },
                        {
                            title: __('Вид платежа'),
                        },
                        {
                            title: __('Тип платежа'),
                        },
                    ],
                },
                {
                    icon: 'menu-quality-control',
                    title: __('Служба контроля качества'),
                    children: [
                        {
                            title: __('Причины черной метки'),
                            permission: 'black-mark-reasons.access',
                            route: {
                                name: 'black-mark-reasons'
                            },
                        },
                        {
                            title: __('Причины обращения в СКК'),
                            permission: 'skk-reasons.access',
                            route: {
                                name: 'skk-reasons'
                            },
                        },
                    ],
                },
                {
                    icon: 'menu-marketing',
                    title: __('Маркетинг'),
                    children: [
                        {
                            title: __('Подрядчики'),
                        },
                        {
                            title: __('Виды рекламы'),
                        },
                        {
                            title: __('Источники информации'),
                            permission: [
                                'information-sources.access',
                                'information-sources.access-clinic',
                            ],
                            route: {
                                name: 'patient-sources'
                            },
                        },
                        {
                            title: __('Медиапланы'),
                        },
                    ],
                },
                {
                    icon: 'menu-stock',
                    title: __('Склад'),
                    children: [
                        {
                            title: __('Группы медикаментов'),
                        },
                        {
                            title: __('Медикаменты'),
                        },
                        {
                            title: __('Виды номенклатур'),
                        },
                        {
                            title: __('Формы выпуска'),
                        },
                    ],
                },
                {
                    icon: 'report',
                    title: __('Кабинет врача'),
                    permission: 'doctor-cabinet.access',
                    route: {
                        name: 'doctor-schedule'
                    },
                },
                {
                    icon: 'report',
                    title: __('Курсы лечения'),
                },
                {
                    icon: 'menu-cashbox',
                    title: __('Приход врача'),
                    permission:  'doctor-cabinet.start-course',
                    route: {
                        name: 'doctor-list-cashier'
                    },
                },
            ]),
        };
    },
    methods: {
        logoutConfirmation() {
            if (lts.cashierCheckboxCashboxes && lts.cashierCheckboxCashboxes.length) {
                this.$confirm(__('Внимание! Не закрыта касса Checkbox, Вы уверены что хотите выйти с учетной записи?'), () => {
                    this.logout();
                });
            } else {
                this.logout();
            }
        },
        logout() {
            let warning = getCloseWarning();
            if (warning === null) {
                this.doLogout();
            } else {
                this.$confirm(warning, () => {
                    this.doLogout();
                }, {
                    confirmBtnText: __('Выйти'),
                });
            }
        },
        doLogout() {
            this.$confirmNavigation(false);
            makeUnsafeAll();
            this.$eventHub.$emit('logout');
            _.waitUntil(() => axios.countPendingRequests() === 0).then(() => {
                this.$store.commit('logout');
                this.$router.push({name: 'login'});
            });
        },
    },
}
</script>
