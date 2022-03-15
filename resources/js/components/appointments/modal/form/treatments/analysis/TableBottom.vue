<template>
    <table class="vuetable ui blue unstackable celled table fixed text-left analysis-table-wrapper">
        <div style="height:150px; overflow:auto;">
            <table class="vuetable table bb-0">
                <thead>
                    <tr>
                        <th class="sticky-header">{{ __('Дата сдачи') }}</th>
                        <th class="sticky-header">{{ __('Код лаборатории') }}</th>
                        <th class="sticky-header">{{ __('Код клиники') }}</th>
                        <th class="sticky-header">{{ __('Название анализов') }}</th>
                        <th class="sticky-header">{{ __('Кол-во дней на анализ') }}</th>
                        <th class="sticky-header">{{ __('Кол-во') }}</th>
                        <th class="sticky-header text-right">{{ __('Стоимость, грн') }}</th>
                        <th class="sticky-header">{{ __('Скидка') }}</th>
                        <th class="text-center sticky-header"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, index) in rows" :key="row.analysis_id">
                        <td width="14%">{{ $formatter.dateFormat(row.date_pass) }}</td>
                        <td width="8%">{{ row.analysis.laboratory_code }}</td>
                        <td width="8%">{{ row.analysis.clinic.code }}</td>
                        <td width="25%">{{ row.analysis.name }}</td>
                        <td width="12%">{{ row.analysis.clinic.duration_days }}</td>
                        <td width="10%" class="no-ellipsis">
                            <el-input-number
                                v-model="row.quantity"
                                controls-position="right"
                                :step="1"
                                :min="1"
                                class="text-right input-tiny"
                                @change="costChanged(row)" />
                        </td>
                        <td width="10%" class="text-right">{{ row.cost }}</td>
                        <td width="10%" class="no-ellipsis">
                            <el-input-number
                                v-model="row.discount"
                                controls-position="right"
                                :step="0.01"
                                :min="0"
                                :max="100"
                                :disabled="row.by_policy == true"
                                class="text-right input-tiny"
                                @change="costChanged(row)" />
                        </td>
                        <td width="3%" class="text-center no-ellipsis">
                            <span class="" @click="toggleSelection(row, index)">
                                <svg-icon name="delete" class="icon-small icon-blue"></svg-icon>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </table>
</template>
<script>
import RowMixin from './mixin/row';
export default {
    mixins: [RowMixin],
    props: {
        checked: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        costChanged(row) {
            this.$emit('cost-changed', row);
        },
    }
}
</script>
