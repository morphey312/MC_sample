<template>
    <table class="vuetable ui blue unstackable celled table fixed text-left analysis-table-wrapper">
        <div>
            <table class="vuetable table bb-0">
                <thead>
                    <tr>
                        <th>{{ __('Январь') }}</th>
                        <th>{{ __('Февраль') }}</th>
                        <th>{{ __('Март') }}</th>
                        <th>{{ __('Апрель') }}</th>
                        <th>{{ __('Май') }}</th>
                        <th>{{ __('Июнь') }}</th>
                        <th>{{ __('Июль') }}</th>
                        <th>{{ __('Август') }}</th>
                        <th>{{ __('Сентябрь') }}</th>
                        <th>{{ __('Октябрь') }}</th>
                        <th>{{ __('Ноябрь') }}</th>
                        <th>{{ __('Декабрь') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td width="8%">
                            <month-input :model="model" prop="january" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="february" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="march" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="april" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="may" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="june" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="july" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="august" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="september" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="october" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="november" />
                        </td>
                        <td width="8%">
                            <month-input :model="model" prop="december" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </table>
</template>
<script>
import MonthInput from './MonthInput.vue';

export default {
    props: {
        model: Object,
    },
    components: {
        MonthInput,
    },
}
</script>