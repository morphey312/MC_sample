<template>
    <div>
        <div class="flex-line bold-text big-text">
            <div>
                {{ 'УВАГА!!! При оплаті данного рахунку в призначенні платежу вказуйте Ваше прізвище та номер амбулаторної картки' }}
            </div>
        </div>
        <div class="bold-text mt-20">
            <div class="text-center">
                {{ 'Зразок заповнення платіжного доручення' }}
            </div>
        </div>
        <div class="border-content">
            <div class="flex-line">
                <div>
                    {{ 'Отримувач' }}
                </div>
                <div class="bold-text ml-30">
                    {{ reciever.name }}
                </div>
            </div>
            <div class="flex-line">
                <div>
                    {{ 'Код' }}
                </div>
                <div class="border-content ml-30 bold-text">
                    {{ reciever.edrpou }}
                </div>
            </div>
            <div class="flex-line">
                <div>
                    <div>
                        {{ 'Банк отримувача' }}
                    </div>
                    <div class="pb-10 border-bottom bold-text">
                        {{ reciever.bank }}
                    </div>
                </div>
                <div class="right-content">
                    <div>
                        {{ 'КРЕДИТ рахунок №' }}
                    </div>
                    <div class="border-content bold-text">
                        {{ reciever.bank_account }}
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="border-bottom bold-text pb-10 mt-20">
                {{ `Рахунок на оплату № ${numberOfPayment} від ${dateOfPayment}` }}
            </div>
        </div>
        <div class="flex-line mt-20">
            <div>
                {{ 'Постачальник' }}
            </div>
            <div class="ml-30" style="white-space: pre;">
                 <ul class="unstyled-list first-bold-li">
                        <li v-for="(item, index) in getFormatedAdditional" :key="index">{{ item }}</li>
                    </ul>
            </div>
        </div>
        <div class="flex-line">
            <div>
                {{ 'Покупець' }}
            </div>
            <div class="ml-30 bold-text">
                {{ patientName }}
            </div>
        </div>
        <div class="flex-line mt-20">
            <div>
                {{ 'Договір' }}
            </div>
            <div class="ml-30">
                {{ __('Амбулаторная карта') + ' ' + appointment.card_number }}
            </div>
        </div>
        <div class="mt-20">
            <table class="printable-table">
                <thead>
                    <tr>
                        <th>
                            №
                        </th>
                        <th>
                            {{ 'Товари (роботи, послуги)' }}
                        </th>
                        <th>
                            {{ 'Кіл-сть' }}
                        </th>
                        <th>
                            {{ 'Од.' }}
                        </th>
                        <th>
                            {{ 'Ціна' }}
                        </th>
                        <th>
                            {{ 'Сума без знижки' }}
                        </th>
                        <th>
                            {{ 'Сума знижки' }}
                        </th>
                        <th>
                            {{ 'Сума' }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(service, index) in services"
                        :key="index">
                        <td>
                            {{ index + 1 }}
                        </td>
                        <td>
                            {{ service.name }}
                        </td>
                        <td>
                            {{ service.qty }}
                        </td>
                        <td>
                            {{ 'грн' }}
                        </td>
                        <td>
                            {{ service.price }}
                        </td>
                        <td>
                            {{ service.fullPrice }}
                        </td>
                        <td>
                            {{ service.discount }}
                        </td>
                        <td>
                            {{ service.cost }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="flex-line">
            <div class="right-content total bold-text">
                <div>{{ 'Всього' }}</div>
                <div>{{ total }}</div>
                <div>{{ totalDiscount }}</div>
                <div>{{ totalWithDiscount }}</div>
            </div>
        </div>
        <div>
            {{ `Всього найменувань ${services.length} на суму ${totalWithDiscount}` }}
        </div>
        <div class="flex-line border-bottom bold-text pb-10 mt-20 big-text">
            {{ totalInWords }}
        </div>
        <div class="flex-line">
            <div class="right-content total mt-20">
                <div>{{ 'Виписав(ла):' }}</div>
                <div class="sign"></div>
            </div>
        </div>
    </div>
</template>

<script>
import BaseDocument from './BaseDocument.vue';

export default {
    extends: BaseDocument,
    computed: {
        numberOfPayment() {
            const lastPayment = this.appointment.latest_payment;
            if (lastPayment) {
                return lastPayment.number;
            }
            return this.appointment.card_number.split('-')[0] + '' + this.$moment().format('mmss') + '/' + this.index;
        },
        dateOfPayment() {
            return this.$moment(this.appointment.date).format('DD MMMM YYYY');
        },
    }
}
</script>
