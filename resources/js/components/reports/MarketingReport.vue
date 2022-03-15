<script>
import DefaultReport from './Default.vue';
import citiesGenerator from './generators/marketing/cities';
import totalsGenerator from './generators/marketing/totals';
import MarketingCitiesReportRepository from '@/repositories/reports/marketing/cities';
import MarketingTotalsReportRepository from '@/repositories/reports/marketing/totals';

export default {
    extends: DefaultReport,
    data(){
        return {
            name: __('отдела маркетинга'),
            permission: 'marketing-reports',
            rows: [
                {
                    id: 1,
                    visible: this.$can('marketing-reports.cities'),
                    name: __('Города'),
                    fileName: __('Отчет - Маркетинг - Города.xlsx'),
                    repository: new MarketingCitiesReportRepository(),
                    generator: citiesGenerator,
                },
                {
                    id: 2,
                    visible: this.$can('marketing-reports.totals'),
                    name: __('Сводная итого'),
                    fileName: __('Отчет - Маркетинг - Сводная итого.xlsx'),
                    repository: new MarketingTotalsReportRepository(),
                    generator: totalsGenerator,
                },
            ].filter(i => i.visible === true),
        }
    },
};
</script>
