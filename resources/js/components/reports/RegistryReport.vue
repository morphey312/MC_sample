<script>
const Excel = require('exceljs');

import DefaultReport from './Default.vue';



export default {
    extends: DefaultReport,
    data(){
        return {
            name: __('Регистратура'),
            rows: [
                /* {
                    id: 1,
                    name: __('Отчеты полоски'),
                },
                {
                    id: 2,
                    name: __('Бонусы регистратуры'),
                },
                {
                    id: 3,
                    name: __('Листы записей пациентов')
                } */
            ]
        }
    }
};
</script>