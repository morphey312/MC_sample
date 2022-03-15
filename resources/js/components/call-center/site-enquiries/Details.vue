<template>
    <table class="vuetable unstackable celled table selectable-data">
        <tr>
            <th width="30%">{{ __('Тип заявки') }}</th><td>{{ $handbook.getOption('enquiry_type', enquiry.category) }}</td>
        </tr>
        <tr>
            <th>{{ __('Имя') }}</th><td>{{ enquiry.name }}</td>
        </tr>
        <tr>
            <th>Email</th><td><a :href="`mailto:${enquiry.email}`">{{ enquiry.email }}</a></td>
        </tr>
        <tr>
            <th>{{ __('Номер телефона') }}</th><td>{{ $formatter.phoneNumberFormat(enquiry.phone_number) }}</td>
        </tr>
        <tr v-if="isAppointmentEnquiry || isCovidTest || isTomography">
            <th>{{ __('Номер карты') }}</th><td>{{ enquiry.card_number }}</td>
        </tr>
        <tr>
            <th>{{ __('Клиника') }}</th><td>{{ enquiry.clinic_name }}</td>
        </tr>
        <tr v-if="isAppointmentEnquiry">
            <th>{{ __('Специализация') }}</th><td>{{ enquiry.specialization_name }}</td>
        </tr>
        <tr v-if="isAppointmentEnquiry">
            <th>{{ __('Врач') }}</th><td>{{ enquiry.doctor_name }}</td>
        </tr>
        <tr v-if="isQuestion">
            <th>{{ __('Тема обращения') }}</th><td>{{ enquiry.subject }}</td>
        </tr>
        <tr>
            <th>{{ __('Примечание') }}</th><td class="no-ellipsis">{{ enquiry.notes }}</td>
        </tr>
        <template v-if="isAppointmentEnquiry || isCovidTest || isTomography">
            <tr>
                <th>{{ __('Желаемая дата записи') }}</th><td>{{ $formatter.datetimeFormat(enquiry.date) }}</td>
            </tr>
        </template>
        <template v-else-if="isPostPayment">
            <tr>
                <th>{{ __('Дата и время записи') }}</th><td>{{ $formatter.datetimeFormat(enquiry.appointment.date + ' ' + enquiry.appointment.start) }}</td>
            </tr>
            <tr>
                <th>{{ __('Пациент') }}</th><td>{{ enquiry.appointment.patient_name }}</td>
            </tr>
            <tr>
                <th>{{ __('Врач') }}</th><td>{{ enquiry.appointment.doctor_name }}</td>
            </tr>
        </template>
        <template v-if="isAppointmentEnquiry || isCovidTest || isTomography || isPostPayment">
            <tr>
                <th>{{ __('Статус оплаты') }}</th><td>
                    {{ $handbook.getOption('enquiry_payment_status', enquiry.payment_status) }}
                    {{ enquiry.order_id ? (__(', ID транзакции') + ' ' + enquiry.order_id) : '' }}
                </td>
            </tr>
            <template v-if="enquiry.payed > 0">
                <tr>
                    <th>{{ __('Стоимость услуг') }}</th><td>{{ $formatter.numberFormat(enquiry.payed) }}</td>
                </tr>
            </template>
        </template>

        <tr>
            <th>{{ __('Сайт') }}</th>
            <td class="no-ellipsis">
                <a v-if="enquiry.referer" :href="toValidUrl(enquiry.referer)" target="_blank">
                    {{ enquiry.referer }}
                </a>
            </td>
        </tr>
        <tr>
            <th>{{ __('ID клиента:') }}</th>
            <td class="no-ellipsis">
                {{ enquiry.client_id }}
            </td>
        </tr>
        <tr>
            <th>{{ __('Токен:') }}</th>
            <td class="no-ellipsis">
                {{ enquiry.token }}
            </td>
        </tr>
    </table>
</template>

<script>
import CONSTANT from '@/constants';

export default {
    props: {
        enquiry: Object,
    },
    computed: {
        isAppointmentEnquiry() {
            return this.enquiry.category === CONSTANT.SITE_ENQUIRY.TYPE.APPOINTMENT;
        },
        isPriceEnquiry() {
            return this.enquiry.category === CONSTANT.SITE_ENQUIRY.TYPE.PRICE;
        },
        isQuestion() {
            return this.enquiry.category === CONSTANT.SITE_ENQUIRY.TYPE.QUESTION;
        },
        isCovidTest() {
            return this.enquiry.category === CONSTANT.SITE_ENQUIRY.TYPE.COVID_TEST;
        },
        isTomography() {
            return this.enquiry.category === CONSTANT.SITE_ENQUIRY.TYPE.TOMOGRAPHY;
        },
        isPostPayment() {
            return this.enquiry.appointment != null;
        },
    },
    methods: {
        toValidUrl(url) {
            if (url.indexOf('://') !== -1) {
                return url;
            }
            return 'http://' + url;
        },
    },
}
</script>
