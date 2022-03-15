export default {
    data() {
        return {
            analysesExportFields: [
                {title: __('КЛИНИКИ'), name: 'clinics'},
                {title: __('ЛАБОРАТОРИЯ'), name: 'laboratory', width: 20},
                {title: __('КОД ЛАБОРАТОРИИ'), name: 'laboratory_code'},
                {title: __('КОД КЛИНИКИ'), name: 'code', width: 15},
                {title: __('АНАЛИЗ'), name: 'analysis_name', width: 25},
                {title: __('КОЛ-ВО ДНЕЙ ДЛЯ ВЫПОЛНЕНИЯ АНАЛИЗА'), name: 'duration', width: 10},
                {title: __('ЦЕНА'), name: 'price', width: 10},
                {title: __('РЕКОМЕНДОВАННАЯ ЦЕНА'), name: 'recommended_price', width: 10},
                {title: __('ВАЛЮТА'), name: 'currency', width: 10},
                {title: __('ДАТА'), name: 'date', width: 15},
            ]
        }
    }
}
