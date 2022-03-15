<script>
import DefaultReport from './Default.vue';
import OperatorsReportRepository from '@/repositories/reports/call-center/operators';
import SubjectsReportRepository from '@/repositories/reports/call-center/subjects';
import CallsIncomeReportRepository from '@/repositories/reports/call-center/calls-income';
import SlicesReportRepository from '@/repositories/reports/call-center/slices';
import RefusedTreatmentReportRepository from '@/repositories/reports/call-center/refused-treatment';
import OperatorBonusesReportRepository from '@/repositories/reports/call-center/operator-bonuses';
import CollectorsReportRepository from '@/repositories/reports/call-center/collectors';
import refusedTreatmentGenerator from './generators/call-center/refused-treatment';
import operatorsGenerator from './generators/call-center/operators';
import subjectsGenerator from './generators/call-center/subjects';
import callsIncomeGenerator from './generators/call-center/calls-income';
import slicesGenerator from './generators/call-center/slices';
import operatorBonusesGenerator from './generators/call-center/operator-bonuses';
import collectorsGenerator from './generators/call-center/collectors';

export default {
    extends: DefaultReport,
    data(){
        return {
            name: __('контактного центра'),
            permission: 'call-center-reports',
            rows: [
                {
                    id: 1,
                    visible: this.$can('call-center-reports.operators'),
                    name: __('Операторы'),
                    fileName: __('Отчет - Операторы.xlsx'),
                    repository: new OperatorsReportRepository(),
                    generator: operatorsGenerator,
                },
                {
                    id: 2,
                    visible: this.$can('call-center-reports.subjects'),
                    name: __('Тематика'),
                    fileName: __('Отчет - Тематика.xlsx'),
                    repository: new SubjectsReportRepository(),
                    generator: subjectsGenerator,
                },
                {
                    id: 4,
                    visible: this.$can('call-center-reports.calls-income'),
                    name: __('Звонки/Записи/Приходы'),
                    fileName: __('Отчет - Звонки, Записи, Приходы.xlsx'),
                    repository: new CallsIncomeReportRepository(),
                    generator: callsIncomeGenerator,
                },
                {
                    id: 5,
                    visible: this.$can('call-center-reports.slices'),
                    name: __('Полоски'),
                    fileName: __('Отчет - Полоски.xlsx'),
                    repository: new SlicesReportRepository(),
                    generator: slicesGenerator,
                },
                {
                    id: 8,
                    visible: this.$can('call-center-reports.refuse-treanment'),
                    name: __('Не взявшие лечение'),
                    fileName: __('Отчет - Не взявшие лечение.xlsx'),
                    repository: new RefusedTreatmentReportRepository(),
                    generator: refusedTreatmentGenerator,
                },
                {
                    id: 9,
                    visible: this.$can('call-center-reports.bonuses'),
                    name: __('Бонусы операторов'),
                    fileName: __('Отчет - Бонусы операторов.xlsx'),
                    repository: new OperatorBonusesReportRepository(),
                    generator: operatorBonusesGenerator,
                    all_clinics: true,
                },
                {
                    id: 10,
                    visible: this.$can('call-center-reports.collectors'),
                    name: __('Отчет по коллекторам'),
                    fileName: __('Отчет по коллекторам.xlsx'),
                    repository: new CollectorsReportRepository(),
                    generator: collectorsGenerator,
                },
            ].filter(i => i.visible === true),
        };
    },
};
</script>