<template>
    <div class="page">
        <div class="flex-line line-between">
            <div>
                <div>
                    {{ 'Затверджую' }}
                </div>
                <div class="mt-20">
                    {{ reciever.signer_position }}
                </div>
                <div>
                    {{ reciever.name }}
                </div>
            </div>
            <div class="right-content">
                <div>
                    {{ 'Затверджую' }}
                </div>
                <div class="mt-20">
                    {{ patientName }}
                </div>
            </div>
        </div>
        <div class="flex-line line-between">
            <div class="sign-wrapper">
                <div class="sign"></div>
            </div>
            <div class="sign-wrapper right-content">
                <div class="sign"></div>
            </div>
        </div>
        <div class="flex-line">
            <div class="mt-20">
                {{ signerName }}
            </div>
        </div>
        <div>
            <div class="bold-text border-bottom mt-20">
                <div>
                    {{ 'АКТ надання послуг' }}
                </div>
                <div class="border-bottom">
                    {{ __('№') + ' ' + numberOfAct + ' ' + 'від' + ' ' + dateOfAct }}
                </div>
            </div>
        </div>
        <div class="flex-line">
            <div class="mt-20">
                {{ headTable }}
            </div>
        </div>
        <div class="flex-line">
            <div class="flex-line ml-30">
                {{ 'Договір: ' }}
                <div class="ml-30">
                    {{ __('амбулаторная карта') + ' ' + appointment.card_number }}
                </div>
            </div>
        </div>
        <div>
            <div class="mt-20">
                {{ 'Виконавцем були виконані наступні работи(надані такі послуги):' }}
            </div>
        </div>
        <div>
            <table class="printable-table">
                <thead>
                    <tr class="bold-text">
                        <th>
                            №
                        </th>
                        <th>
                            {{ 'Назва робіт, послуг' }}
                        </th>
                        <th>
                            {{ 'Кіл-ть' }}
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
                <div>
                    {{ __('Всего') }}
                </div>
                <div>
                    {{ total }}
                </div>
                <div>
                    {{ totalDiscount }}
                </div>
                <div>
                    {{ totalWithDiscount }}
                </div>
            </div>
        </div>
        <div>
            <div>
                {{ 'Загальна вартість робіт (послуг) склала' + ' ' + totalInWords }}
            </div>
        </div>
        <div>
            <div>
                {{ 'Замовник претензій по об\'єму, якості та строкам виконання робіт (надання послуг) не має.' }}
            </div>
        </div>
        <div>
            <div class="mt-20 border-bottom pb-20">
                {{ 'Місце складання:' + ' ' + clinic.name }}
            </div>
        </div>
        <div class="flex-line mt-20">
            <div>
                <div class="bold-text">
                    {{ 'Від виконавця*' }}
                </div>
                <div class="sign-wrapper">
                    <div class="sign"></div>
                </div>
            </div>
            <div class="right-content">
                <div class="bold-text">
                    {{ 'Від замовника' }}
                </div>
                <div class="sign-wrapper">
                    <div class="sign"></div>
                </div>
            </div>
        </div>
        <div class="flex-line">
            <div class="mt-20">
                <div class="bold-text">
                    {{ reciever.signer_position + ' ' + signerName }}
                </div>
                <div class="small-text">{{ '*Відповідальний за здійснення господарської операції і правильність її виконання' }}</div>
            </div>
        </div>
        <div class="flex-line">
            <div>
                <div class="bold-text">
                    {{ $formatter.dateFormat(appointment.date) }}
                </div>
                    <ul class="unstyled-list">
                        <li v-for="(item, index) in getFormatedAdditional" :key="index">{{ item }}</li>
                    </ul>
            </div>
            <div class="right-content">
                <div class="bold-text">
                    {{ $formatter.dateFormat(appointment.date) }}
                </div>
                <div>
                    {{ patientName }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import BaseDocument from './BaseDocument.vue';

export default {
    extends: BaseDocument,
    computed: {
        numberOfAct() {
            const lastAct = this.appointment.latest_act;
            if (lastAct) {
                return lastAct.number;
            }
            return this.appointment.card_number.split('-')[0] + '' + this.$moment().format('mmss') + '/' + this.index;
        },
        dateOfAct() {
            return this.$moment(this.appointment.date).format('DD MMMM YYYY');
        },
        headTable() {
            return __('Ми, що нижче підписалися, представник Замовника {patient_fullname}, ' +
                'з однго боку, та представник Виконавця {reciever_full} {signer_position} {signer_full}, з іншого боку, ' +
                'склали цей акт про те, що на підставі наданних документів:', {
                patient_fullname: this.patientName,
                reciever_full: this.reciever.name,
                signer_position: this.reciever.signer_position,
                signer_full: this.signerName
            });
        },
    },
}
</script>