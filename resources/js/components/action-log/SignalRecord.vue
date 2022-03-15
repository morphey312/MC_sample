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
            repository: new ActionLogRepository({filters: {signal_record: this.id}}),
            attributes: {
                blood_group: {
                    label: __('Группа крови'),
                    formatter: (val) => {
                        return this.$handbook.getOption('blood_group', val);
                    }
                },
                rhesus_factor: {
                    label: __('Резус фактор'),
                    formatter: (val) => {
                        return this.$handbook.getOption('rhesus_factor', val);
                    }
                },
                diabetes: {
                    label: __('Сахарный диабет'),
                    formatter: (val) => {
                        return this.$handbook.getOption('diabetes', val);
                    }
                },
                transfusion: {
                    label: __('Переливание крови'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                onco_observation_gyn: {
                    label: __('Онкоскрининг ГИН'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                onco_observation_gyn_date: {
                    label: __('Онкоскрининг ГИН (дата)')
                },
                patient_feedback: {
                    label: __('Отзыв о пациенте')
                },
                onco_observation_pro: {
                    label: __('Онкоскрининг ПРО'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                onco_observation_pro_date: {
                    label: __('Онкоскрининг ПРО (дата)')
                },
                onco_observation_uro: {
                    label: __('Онкоскрининг УРО'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                onco_observation_uro_date: {
                    label: __('Онкоскрининг УРО (дата)')
                },
                onco_observation_ren: {
                    label: __('Скрининг ОГП'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                onco_observation_ren_date: {
                    label: __('Скрининг ОГП (дата)')
                },
                onco_observation_vaserman: {
                    label: __('Реакция Вассермана'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                onco_observation_vaserman_date: {
                    label: __('Реакция Вассермана (дата)')
                },
                onco_observation_vil: {
                    label: __('Cкрининг ВИЛ'),
                    formatter: (val) => {
                        return this.$formatter.boolFormat(val);
                    }
                },
                onco_observation_vil_date: {
                    label: __('Cкрининг ВИЛ (дата)')
                },
                transfusion_comment: __('Переливание крови (комментарий)'),
                drug_intolerance: __('Непереносимость лекарств'),
                infections: __('Инфекционные заболевания'),
                surgical_interventions: __('Хирургические вмешательства'),
                allergic_history: __('Аллергологический анамнез'),
            },
        };
    },
};
</script>
