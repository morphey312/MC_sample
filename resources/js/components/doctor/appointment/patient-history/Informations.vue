<template>
    <div>
        <div v-for="(item, index) in items" :key="index" class="history-entry">
            <item-row
                :row="item"
                :titleList="titleList"
                @show-details="showDetails"
            />
        </div>
    </div>
</template>
<script>
import ItemRow from './Row.vue';

export default {
    components: {
        ItemRow,
    },
    props: {
        items: Array,
    },
    data() {
        return {
            titleList: {
                card_assignment: {
                    assigned_procedures: __('Назначены процедуры'),
                    assigned_physiotherapies: __('Назначены физиопроцедуры'),
                    assigned_medicines: __('Назначены медикаменты'),
                    analysis_results: __('Назначены анализы'),
                    assigned_diagnostics: __('Назначена аппаратная диагностика'),
                    surgery_base_services: __('Назначена операция'),
                    surgery_services: __('Назначены операционные мероприятия'),
                },
                diary_record: __('Запись в дневнике'),
                service_record: __('Служебная запись в дневнике'),
                condition_record: __('Запись в дневнике'),
                manipulation_record: __('Проведенные манипуляции'),
                outpatient_record: __('Изменения в амбулаторной карте'),
                protocol_record: __('Заполнен протокол исследований'),
                consultation_record: __('Назначены консультации врачей'),
                next_visit: __('Назначен следующий визит'),
                patient_card_document: __('Добавлен документ:'),
                patient_card_printed_document: __('Распечатан документ:'),
                outclinic_protocol_record: __('Добавлены результаты исследований:'),
                treatment_assignment: {
                    initial: __('Назначено лечение'),
                    repeated: __('Добавлено лечение'),
                },
                default: __('Назначения'),
            },
        }
    },
    methods: {
        showDetails(row) {
            this.$emit('show-details', row);
        },
    }
}
</script>
