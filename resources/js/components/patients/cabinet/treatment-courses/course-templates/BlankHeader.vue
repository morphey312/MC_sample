<!-- /** @scan-translations-off */ -->
<template>
    <div class="blank-header">
        <div class="header-section">
            <div>{{ clinic.authority_name}}</div>
            <div>{{ clinic.official_name }}, {{ clinicFullAddress}}</div>
            <div>{{ __("Код ЕГРПОУ") }} {{ clinic.edrpou }}</div>
        </div>
        <div class="header-section text-center">
            <div><b>{{ __('МЕДИЧНА ДОКУМЕНТАЦІЯ') }}</b></div>
            <div>
                {{ __('Форма первинної облікової документації') }} <br>
                <span><b>{{ blankData.blankNumber ? '№' + blankData.blankNumber : '' }}</b></span><br>
                <span><b>{{ __('ЗАТВЕРДЖЕНО') }}</b></span><br>
                {{ __('Наказ МОЗ України') }}<br>
                {{ blankData.date || '' }} № {{ blankData.number || '' }}
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        clinic: {
            type: Object,
            default: () => ({}),
        },
        blankData: {
            type: Object,
            default: () => ({}),
        },
    },
    computed: {
        clinicFullAddress() {
            if (this.clinic && this.clinic.address) {
                let address = this.clinic.address;
                return (address.zip ? address.zip + ', ' : '') + address.address + (address.street ? ', ' + address.street : '');
            }
            return '';
        },
    },
}
</script>