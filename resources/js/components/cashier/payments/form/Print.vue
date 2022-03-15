<template>
    <div>
        <div
            v-for="(check, index) in checks"
            :key="index"
            class="check-wrapper">
            <div v-if="isCopy" class="line text-center" style="font-weight: bold;">
                {{ __('КОПІЯ') }}
            </div>
            <div class="line">{{ $formatter.dateFormat(check.created, 'DD.MM.YYYY HH:mm:ss') }}</div>
            <div class="line text-center">
                {{ getClinicName(check) }}
            </div>
            <div class="line text-center">
            </div>
            <div class="flex line">
                <div>
                    &laquo;<span class="underline">&nbsp;{{ $formatter.dateFormat(check.created, 'DD') }}&nbsp;</span>&raquo;
                    <span class="underline">&nbsp;&nbsp;{{ $formatter.dateFormat(check.created, 'MM') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        {{ $formatter.dateFormat(check.created, 'YYYY') }}&nbsp;&nbsp;&nbsp;&nbsp;</span>{{ __('&nbsp;р.') }}
                </div>
                <div>{{ check.clinic.city }}</div>
            </div>
            <div class="text-center line">
                {{ __('Звіт про прийняті оплати №') }}<span class="underline">&nbsp;&nbsp;{{ check.checkId }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </div>
            <div class="text-center line">
                {{ __('Пацієнт (номер амбулаторної карти):') }}
            </div>
            <div class="card-line text-center">{{ check.cardNumber }}</div>
            <table class="table line">
                <thead>
                    <tr>
                        <th>{{ __('Послуга') }}</th>
                        <th>{{ __('Сума') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(service, index) in check.services" :key="index">
                        <td>{{ service.name }}</td>
                        <td>{{ $formatter.numberFormat(service.payed_amount) }}</td>
                    </tr>
                </tbody>
            </table>
            <table class="summary">
                <tr>
                    <td>{{ __('Сума до сплати') }}</td>
                    <td>{{ $formatter.numberFormat(getCheckTotal(check.services)) }}</td>
                </tr>
            </table>
            <div class="flex line">
                <span>{{ __('Касир&nbsp;') }}</span>
                <div style="border-bottom: 1px solid #000;" width="70%"></div>
            </div>
            <div class="line">
                {{ __('Шановний пацієнте, цей документ слугує для звіряння розрахунків за надані послуги.') }}
                {{ __('Просимо зберігати звіт до закінчення лікування.') }}
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        checks: {
            type: Object,
            default: () => ({}),
        },
        isCopy: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        getCheckTotal(services) {
            return services.reduce((total, item) => {
                return total + (item.payed_amount ? item.payed_amount : 0);
            }, 0);
        },
        getClinicName(check) {
            if (check.money_reciever && _.isFilled(check.money_reciever.name)) {
                return check.money_reciever.name;
            }
            if (check.clinic.official_name && _.isFilled(check.clinic.official_name)) {
                return check.clinic.official_name;
            }
            return check.clinic.name;
        },
    },
}
</script>
