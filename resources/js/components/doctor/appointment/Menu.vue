<template>
    <div :class="classListMenu">
        <div class="additional-menu">
            <div class="menu-addon">
                <div class="line">{{ __('Кабинет врача:') }}</div>
                <div class="line">{{ userName }}</div>
            </div>
            <el-menu
                ref="menu"
                class="side-menu">
                <menu-item
                    v-for="(item, index) in items"
                    :item="item"
                    :num="index"
                    :key="index" />
            </el-menu>
            <div class="mob-menu-toggle">
                <a
                    href="#"
                    class="toggle-menu-btn "
                    :class="arrowClass"
                    @click.prevent="toggleMobileAppointmentMenu"
                ></a>
            </div>
        </div>
    </div>
</template>

<script>
import MenuMixin from '@/mixins/menu';
import ModalsMixin from './mixins/modals';
import SpecializationRepository from '@/repositories/specialization';

export default {
    mixins: [
        MenuMixin,
        ModalsMixin,
    ],
    props: {
        appointment: Object,
        activeCard: Object,
        analyses: Array,
        medicines: Array,
        procedures: Array,
        physiotherapies: Array,
        nextVisit: Object,
        outpatientRecord: Object,
        diagnostics: Array,
        surgeryBaseServices: Array,
        surgeryServices: Array,
        outclinicDiagnostics: Array,
        protocols: Array,
        consultations: Array,
        readonly: Boolean,
        doctorSpecializations: Array,
        insurancePolicy: Object,
        clinicRequisites: Object,
        isSurgery: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        userName() {
            return this.$store.state.user.full_name;
        },
        classListMenu() {
            return window.innerWidth <= 1024 ? 'additional-menu-wrapper__appointment toggle-menu__appointment' : 'additional-menu-wrapper';
        },
        arrowClass() {
            return this.isMenuCollapsed ? 'el-icon-arrow-right' : 'el-icon-arrow-left';
        }
    },
    data() {
        return {
            items: this.getMenuItems([{
                title: __('Загрузка...'),
                callback: () => {},
            }]),
            isMenuCollapsed: true,
        };
    },
    mounted() {
        this.loadProtocolSpecializations();
    },
    methods: {
        toggleMobileAppointmentMenu() {
            let menuMobile = document.querySelector('.toggle-menu__appointment');
            if(menuMobile.classList.contains('additional-menu-wrapper__appointment')) {
                menuMobile.classList.toggle('touched');
                this.isMenuCollapsed = !this.isMenuCollapsed;
            }
        },
        loadProtocolSpecializations() {
            let doctorSpecializations = this.$store.state.user.specializations;
            if (doctorSpecializations.length !== 0) {
                let repository = new SpecializationRepository();
                repository.fetchList({has_protocol: 1, id: doctorSpecializations}).then((list) => {
                    let menu = [];
                    list.forEach((item) => {
                        menu.push({
                            title: item.value,
                            callback: () => {
                                this.filloutProtocol(item.id);
                            },
                        });
                    });
                    this.items = this.getMenuItems(menu);
                });
            } else {
                this.items = this.getMenuItems([]);
            }
        },
        getMenuItems(protocolsSpecializations) {
            return this.accessFilter([
                {
                    title: __('Листы записи пациентов'),
                    route: {
                        name: 'doctor-schedule'
                    },
                    icon: 'menu-reports',
                },
                ...(this.isSurgery ? this.getSurgeryItems(protocolsSpecializations) : this.getOrdinaryItems(protocolsSpecializations)),
                {
                    title: __('Шаблоны документов для пациентов'),
                    icon: 'menu-documents',
                    children: [
                        {
                            title: __('Добавить результат исследования'),
                            callback: () => this.addProtocol(),
                            permission: 'doctor-cabinet.add-research',
                        },
                        {
                            title: __('Добавить документ'),
                            callback: () => this.addDocument(),
                            permission: 'doctor-cabinet.add-document',
                        },
                        {
                            title: __('Выдать документ'),
                            callback: () => this.makeIssuePatientDocument(),
                            permission: 'doctor-cabinet.issue-document',
                        }
                    ],
                },
                {
                    title: __('Маршруты пациентов'),
                    icon: 'arrows',
                    callback: () => this.showPatientRoutes(),
                    permission: 'patient-clinic-routes.view',
                },
                {
                    title: __('Назначить дату следующего визита'),
                    icon: 'calendar',
                    callback: () => this.setNextVisit(),
                    permission: 'doctor-cabinet.schedule-visit',
                },
                {
                    title: __('Назначить оплату'),
                    icon: 'dollar-alt',
                    callback: () => this.setExpectedPayment(),
                    permission: 'patient-cabinet.payments',
                },
                {
                    title: __('Личный кабинет пациента'),
                    icon: 'menu-marketing',
                    permission: 'patient-cabinet.access',
                    callback: () => this.goPatientCabinet(),
                },
                {
                    title: __('История пациента'),
                    icon: 'catalogue',
                    callback: () => this.patientHistory(),
                },
                {
                    title: __('Электронная отчетность eHealth'),
                    icon: 'catalogue',
                    permission: 'ehealth-care-episode.access',
                    callback: () => this.episodeOfCare(),
                }
            ]);
        },
        getOrdinaryItems(protocolsSpecializations) {
            return [
                {
                    title: __('Обследования во время приема'),
                    icon: 'menu-appointment-diagnostic',
                    children: [
                        {
                            title: __('Услуги'),
                            callback: () => this.makeServices(),
                            permission: 'doctor-cabinet.diagnostic',
                        },
                        {
                            title: __('Забор анализов'),
                            callback: () => this.makeAnalyses(),
                            permission: 'doctor-cabinet.assign-analyses',
                        },
                    ],
                },
                {
                    title: __('Диагностика'),
                    icon: 'menu-diagnostics',
                    children: [
                        {
                            title: __('Назначить анализы'),
                            callback: () => this.assignAnalysis(),
                            permission: 'doctor-cabinet.assign-analyses',
                        },
                        {
                            title: __('Назначить аппаратную диагностику'),
                            callback: () => this.assignDiagnostic(),
                            permission: 'doctor-cabinet.diagnostic',
                        },
                        {
                            title: __('Назначить консультацию врачей'),
                            callback: () => this.assignConsultation(),
                            permission: 'doctor-cabinet.issue-referral',
                        },
                    ],
                },
                {
                    title: __('Заполнить протокол исследования'),
                    icon: 'menu-doctor-report',
                    children: protocolsSpecializations,
                    permission: 'doctor-cabinet.protocol',
                },
                {
                    title: __('Лечебные мероприятия'),
                    icon: 'menu-stock',
                    children: [
                        {
                            title: __('Назначить лечение'),
                            callback: () => this.assignCourse(),
                            permission: 'doctor-cabinet.start-course',
                        },
                        {
                            title: __('Назначить медикаменты'),
                            callback: () => this.assignMedicine(),
                            permission: 'doctor-cabinet.assign-medicine',
                        },
                        {
                            title: __('Назначить процедуры'),
                            callback: () => this.assignProcedure(),
                            permission: 'doctor-cabinet.assign-procedure',
                        },
                        {
                            title: __('Назначить физиопроцедуры'),
                            callback: () => this.assignPhysiotherapy(),
                            permission: 'doctor-cabinet.assign-therapy',
                        },
                    ],
                },
            ];
        },
        getSurgeryItems(protocolsSpecializations) {
            return [
                {

                    title: __('Осмотр пациента'),
                    icon: 'menu-appointment-diagnostic',
                    children: [
                        {
                            title: __('Услуги во время осмотра'),
                            callback: () => this.surgeyDoctorService(),
                            permission: 'doctor-cabinet.surgery-inspection',
                        },
                        {
                            title: __('Анализы во время осмотра'),
                            callback: () => this.surgeyDoctorAnalyses(),
                            permission: 'doctor-cabinet.surgery-inspection',
                        },
                    ],
                },
                {
                    title: __('Диагностика'),
                    icon: 'menu-diagnostics',
                    children: [
                        {
                            title: __('Назначить консультацию врачей'),
                            callback: () => this.assignSurgeryConsultations(),
                            permission: 'doctor-cabinet.surgery-diagnostic',
                        },
                        {
                            title: __('Назначить диагностику'),
                            callback: () => this.assignSurgeryDiagnostic(),
                            permission: 'doctor-cabinet.surgery-diagnostic',
                        },
                        {
                            title: __('Назначить анализы'),
                            callback: () => this.assignSurgeryAnalyses(),
                            permission: 'doctor-cabinet.surgery-diagnostic',
                        },
                    ],
                },
                {
                    title: __('Операционные мероприятия'),
                    icon: 'menu-stock',
                    children: [
                        {
                            title: __('Назначить операцию'),
                            callback: () => this.assignSurgery(),
                            permission: 'doctor-cabinet.surgery-services',
                        },
                        {
                            title: __('Назначить услуги'),
                            callback: () => this.assignSurgeryServices(),
                            permission: 'doctor-cabinet.surgery-services',
                        },
                        {
                            title: __('Назначить медикаменты'),
                            callback: () => this.assignSurgeryMedicines(),
                            permission: 'doctor-cabinet.surgery-services',
                        },
                        {
                            title: __('Назначить процедуры'),
                            callback: () => this.assignSurgeryProcedure(),
                            permission: 'doctor-cabinet.assign-procedure',
                        },
                        {
                            title: __('Назначить физиопроцедуры'),
                            callback: () => this.assignSurgeryPhysiotherapy(),
                            permission: 'doctor-cabinet.assign-therapy',
                        },
                    ],
                },
                {
                    title: __('Заполнить протокол исследования'),
                    icon: 'menu-doctor-report',
                    children: protocolsSpecializations,
                    permission: 'doctor-cabinet.protocol',
                },
            ];
        },
    },
};
</script>
