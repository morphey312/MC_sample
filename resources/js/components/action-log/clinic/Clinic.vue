<script>
import DefaultLog from '../Default.vue';
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
            repository: new ActionLogRepository({filters: {clinic: this.id}}),
            attributes: {
                not_round_cost: {
                    label: __('Не округлять стоимость'),
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
                city: {
                    label: __('Статус'),
                    formatter: (val) => {
                        return this.$handbook.getOption('city', val);
                    }
                },
                currency: {
                    label: __('Валюта'),
                    formatter: (val) => {
                        return this.$handbook.getOption('currency', val);
                    }
                },
                voip_queue: {
                    label: __('Очередь звонков'),
                    formatter: (val) => {
                        return this.$handbook.getOption('voip_queue', val);
                    }
                },
                kind: {
                    label: __('Тип подразделения'),
                    formatter: (val) => {
                        return this.$handbook.getOption('clinic_kind', val);
                    }
                },
                working_hours: {
                    label: __('Режим работы'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                medicine_stores: {
                    label: __('Медицинские склады'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                additional_contact_phone: __('Дополнительный телефон'),
                contact_phone: __('Контактный телефон'),
                analysis_check_url: __('URL для проверки анализов'),
                authority_name: __('Название органа управления'),
                country_id: __('Страна'),
                email: __('Email'),
                group_id: __('Группа клиник'),
                name: __('Название клиники'),
                country: __('Страна'),
                address: __('Адрес'),
                lat: __('Координаты (широта)'),
                lng: __('Координаты (долгота)'),
                map_link: __('Расположение на карте'),
                msp_id: __('Предоставитель мед. услуг'),
                official_additional: __('Реквизиты'),
                official_name: __('Официальное название клиники'),
                short_name: __('Краткое название'),
                signer: __('Подписант'),
                header: __('Шапка бланка'),
                footer: __('Подвал бланка'),
                questionnaire_blank: __('Бланк анкеты пациента'),
                image_id: __('Фото клиники'),
                signer_position: __('Должность подписанта'),
            },
        };
    },
};
</script>
