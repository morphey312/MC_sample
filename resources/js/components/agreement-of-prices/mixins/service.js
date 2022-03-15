export default {
    data() {
        let extraLang1 = this.getLangBySuffix('lc1');
        let extraLang2 = this.getLangBySuffix('lc2');
        let extraLang3 = this.getLangBySuffix('lc3');

        return {
            serviceExportFields: [
                {title: __('КЛИНИКИ'), name: 'clinics'},
                {title: __('НАЗВАНИЕ ПРАЙСА'), name: 'price_name', width: 25},
                {title: __('СПЕЦИАЛИЗАЦИЯ'), name: 'specialization', width: 20},
                {title: __('КОД'), name: 'code'},
                {title: __('МЕДИЦИНСКАЯ УСЛУГА'), name: 'service_name', width: 45},
                ...(extraLang1 === '' ? [] : [{title: __('МЕДИЦИНСКАЯ УСЛУГА') + ` (${extraLang1})`, name: 'service_name_lc1', width: 45}]),
                ...(extraLang2 === '' ? [] : [{title: __('МЕДИЦИНСКАЯ УСЛУГА') + ` (${extraLang2})`, name: 'service_name_lc2', width: 45}]),
                ...(extraLang3 === '' ? [] : [{title: __('МЕДИЦИНСКАЯ УСЛУГА') + ` (${extraLang3})`, name: 'service_name_lc3', width: 45}]),
                {title: __('НАЗВАНИЕ УСЛУГИ ДЛЯ ЧЕКА'), name: 'service_name_ua', width: 45},
                ...(extraLang1 === '' ? [] : [{title: __('НАЗВАНИЕ УСЛУГИ ДЛЯ ЧЕКА') + ` (${extraLang1})`, name: 'service_name_ua_lc1', width: 45}]),
                ...(extraLang2 === '' ? [] : [{title: __('НАЗВАНИЕ УСЛУГИ ДЛЯ ЧЕКА') + ` (${extraLang2})`, name: 'service_name_ua_lc2', width: 45}]),
                ...(extraLang3 === '' ? [] : [{title: __('НАЗВАНИЕ УСЛУГИ ДЛЯ ЧЕКА') + ` (${extraLang3})`, name: 'service_name_ua_lc3', width: 45}]),
                {title: __('БАЗОВАЯ УСЛУГА'), name: 'is_base', width: 10},
                {title: __('НАЗНАЧЕНИЕ ПЛАТЕЖА'), name: 'payment_destination', width: 20},
                {title: __('ЦЕНА'), name: 'price', width: 10},
                {title: __('РЕКОМЕНДОВАННАЯ ЦЕНА'), name: 'recommended_price', width: 10},
                {title: __('ВАЛЮТА'), name: 'currency', width: 10},
                {title: __('ДАТА'), name: 'date', width: 15},
                {title: __('ТИП УСЛУГИ НА САЙТЕ'), name: 'site_service_type', width: 30},
                {title: __('ОНЛАЙН-ВИДЕОКОНСУЛЬТАЦИЯ'), name: 'is_online', width: 30},
            ]
        }
    },
    methods: {
        getLangBySuffix(suffix) {
            return (this.$store.state.user.langBySuffix(suffix) || {}).short_name || '';
        },
    }
}
