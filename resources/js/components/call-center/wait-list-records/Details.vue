<template>
    <table class="vuetable unstackable celled table">
        <tr>
            <th width="30%">{{ __('Имя') }}</th><td>{{ record.name }}</td>
        </tr>
        <tr>
            <th>Email</th><td><a :href="`mailto:${record.email}`">{{ record.email }}</a></td>
        </tr>
        <tr>
            <th>{{ __('Номер телефона') }}</th><td>{{ $formatter.phoneNumberFormat(record.phone_number) }}</td>
        </tr>
        <tr>
            <th>{{ __('Номер карты') }}</th><td>{{ record.card_number }}</td>
        </tr>
        <tr>
            <th>{{ __('Клиника') }}</th><td>{{ record.clinic_name }}</td>
        </tr>
        <tr>
            <th>{{ __('Специализация') }}</th><td>{{ record.specialization_name }}</td>
        </tr>
        <tr>
            <th>{{ __('Врач') }}</th><td>{{ record.doctor_name }}</td>
        </tr>
        <tr>
            <th>{{ __('Найденная дата') }}</th><td>{{ $formatter.dateFormat(record.found_date) }}</td>
        </tr>
        <tr>
            <th>{{ __('Желаемая дата записи') }}</th>
            <td>{{ $formatter.dateFormat(record.period_from) }} - {{ $formatter.dateFormat(record.period_to) }}</td>
        </tr>
    </table>
</template>
<script>
export default {
    props: {
        record: Object,
    },
}
</script>