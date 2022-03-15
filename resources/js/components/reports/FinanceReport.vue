<script>
import DefaultReport from './Default.vue';
import ScheduleReportRepository from '@/repositories/reports/finance/schedule';
import ServicesReportRepository from "@/repositories/reports/finance/services";
import RedirectReportRepository from '@/repositories/reports/finance/redirect';
import DebitorsReportRepository from '@/repositories/reports/finance/debitor';
import OnlineServiceReportRepository from '@/repositories/reports/finance/online-service';
import InsurancePatientsReportRepository from '@/repositories/reports/finance/insurance-patients';
import servicesGenerator from './generators/finance/services';
import scheduleGenerator from './generators/finance/schedule';
import redirectGenerator from './generators/finance/redirect';
import debitorGenerator from './generators/finance/debitor';
import onlineServiceGenerator from './generators/finance/online-service';
import insurancePatientsGenerator from './generators/finance/insurance-patients';

export default {
    extends: DefaultReport,
    data(){
        return {
            name: __('финансового отдела'),
            permission: 'finance-reports',
            rows: [
                {
                    id: 2,
                    visible: this.$can('finance-reports.schedule'),
                    name: __('График'),
                    fileName: __('Отчет - График.xlsx'),
                    repository: new ScheduleReportRepository(),
                    generator: scheduleGenerator,
                },
                {
                    id: 3,
                    visible: this.$can('finance-reports.services'),
                    name: __('Проданные услуги'),
                    fileName: __('Отчет - Проданные услуги.xlsx'),
                    repository: new ServicesReportRepository(),
                    generator: servicesGenerator,
                    all_clinics: true,
                },
                {
                    id: 4,
                    visible: this.$can('finance-reports.redirects'),
                    name: __('Перенаправления'),
                    fileName: __('Перенаправления.xlsx'),
                    repository: new RedirectReportRepository(),
                    generator: redirectGenerator,
                },
                {
                    id: 5,
                    visible: this.$can('finance-reports.debitors'),
                    name: __('Должники'),
                    fileName: __('Должники.xlsx'),
                    repository: new DebitorsReportRepository(),
                    generator: debitorGenerator,
                },
                {
                    id: 6,
                    visible: this.$can('finance-reports.online-services'),
                    name: __('Онлайн услуги'),
                    fileName: __('Онлайн услуги.xlsx'),
                    repository: new OnlineServiceReportRepository(),
                    generator: onlineServiceGenerator,
                },
                {
                    id: 7,
                    visible: this.$can('finance-reports.insurance-patients'),
                    name: __('Страховые пациенты'),
                    fileName: __('Страховые пациенты.xlsx'),
                    repository: new InsurancePatientsReportRepository(),
                    generator: insurancePatientsGenerator,
                    all_clinics: true,
                },
            ].filter(i => i.visible === true),
        }
    },
};
</script>
