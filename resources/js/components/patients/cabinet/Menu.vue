<template>
    <div class="additional-menu-wrapper">
        <div class="additional-menu">
            <div class="menu-addon">
                <div class="line">{{ __('Кабинет пациента:') }}</div>
                <div class="line">{{ patient.full_name }}</div>
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
        </div>
    </div>
</template>

<script>
import MenuMixin from '@/mixins/menu';

export default {
    mixins: [
        MenuMixin,
    ],
    props: {
        patient: Object,
    },
    data() {
        return {
            items: this.getMenuItems(),
        };
    },
    methods: {
        routeTo(name) {
            return {
                name,
                patientId: this.patient.id,
            };
        },
        getMenuItems() {
            return this.accessFilter([
                {
                    title: __('Информация о пациенте'),
                    route: this.routeTo('patient-cabinet-info'),
                    icon: 'user-alt',
                    permission: 'patient-cabinet.info',
                },
                {
                    title: __('Амбулаторные карты'),
                    route: this.routeTo('patient-cabinet-outpatient'),
                    icon: 'menu-clinics',
                    permission: 'patient-cabinet.outpatient-cards',
                },
                {
                    title: __('Записи/Звонки/Оповещения'),
                    route: this.routeTo('patient-cabinet-calls'),
                    icon: 'call-incoming',
                    permission: 'patient-cabinet.calls-appointments',
                },
                {
                    title: __('Курсы лечения'),
                    route: this.routeTo('patient-cabinet-courses'),
                    icon: 'patient-contacts',
                    permission: 'patient-cabinet.courses',
                },
                {
                    title: __('Медикаментозное лечение'),
                    route: this.routeTo('patient-cabinet-medicines'),
                    icon: 'menu-stock',
                    permission: 'patient-cabinet.courses',
                },
                {
                    title: __('Результаты исследований'),
                    route: this.routeTo('patient-cabinet-protocols'),
                    icon: 'arhive',
                    permission: 'patient-cabinet.protocols',
                },
                {
                    title: __('Результаты анализов'),
                    route: this.routeTo('patient-cabinet-analyses'),
                    icon: 'menu-diagnostics',
                    permission: 'patient-cabinet.analyses',
                },
                {
                    title: __('Оплаты'),
                    route: this.routeTo('patient-cabinet-payments'),
                    icon: 'menu-cashbox',
                    permission: 'patient-cabinet.payments',
                },
                {
                    title: __('История пациента'),
                    route: this.routeTo('patient-cabinet-history'),
                    icon: 'menu-documents',
                    permission: 'patient-cabinet.history',
                },
                {
                    title: __('Документы пациента'),
                    route: this.routeTo('patient-cabinet-documents'),
                    icon: 'menu-documents',
                    permission: 'patient-cabinet.documents',
                }
            ]);
        },
    },
};
</script>
