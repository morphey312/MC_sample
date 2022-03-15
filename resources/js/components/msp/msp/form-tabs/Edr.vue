<template>
    <div>
        <div class="manage-table">
            <table class="vuetable unstackable celled table">
                <tr>
                    <th width="30%">{{ __('Виды деятельности') }}</th>
                    <td class="no-ellipsis">
                        <ul>
                            <li 
                                :key="kved.code"
                                v-for="kved in getAttr('edr_data.kveds')">
                                {{ kved.name }}
                            </li>
                        </ul>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('Адрес регистрации') }}</th>
                    <td class="no-ellipsis">{{ getAttr('edr_data.registration_address.address') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Организационно-правовая форма') }}</th>
                    <td class="no-ellipsis">{{ getLegalForm(getAttr('edr_data.legal_form')) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Полное название') }}</th>
                    <td class="no-ellipsis">{{ getAttr('edr_data.name') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Публичное название') }}</th>
                    <td class="no-ellipsis">{{ getAttr('edr_data.public_name') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Короткое название') }}</th>
                    <td class="no-ellipsis">{{ getAttr('edr_data.short_name') }}</td>
                </tr>
                <tr>
                    <th>{{ __('Статус в ЕГРПОУ') }}</th>
                    <td>{{ getAttr('edr_data.state') ? __('Активно') : __('Неактивно') }}</td>
                </tr>
            </table>
        </div>
        <div class="mt-10 manage-table">
            <table class="vuetable unstackable celled table">
                <tr>
                    <th width="30%">{{ __('Статус в e-Health') }}</th>
                    <td>{{ $handbook.getOption('msp_ehealth_status', getAttr('ehealth_status')) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Просмотрено НСЗУ') }}</th>
                    <td>{{ $formatter.boolFormat(getAttr('nhs_reviewed')) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Верифицировано НСЗУ') }}</th>
                    <td>{{ $formatter.boolFormat(getAttr('nhs_verified')) }}</td>
                </tr>
                <tr>
                    <th>{{ __('Комментарй НСЗУ') }}</th>
                    <td class="no-ellipsis">{{ getAttr('nhs_comment') }}</td>
                </tr>
            </table>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        model: Object,
    },
    methods: {
        getAttr(key) {
            return _.get(this.model.attributes, key);
        },
        getLegalForm(form) {
            return _.get({
                '120': __('Частное предприятие'),
                '140': __('Государственное предприятие'),
                '145': __('Казеное предприятие'),
                '150': __('Коммунальное предприятие'),
                '160': __('Дочернее предприятие'),
                '193': __('Совместное предприятие'),
                '230': __('Акционерное предприятие'),
                '231': __('Открытое акционерное общество'),
                '232': __('Закрытое акционерное общество'),
                '235': __('Государственная акционерная компания (общество)'),
                '240': __('Общество с ограниченной ответственностью'),
                '250': __('Общество с дополнительной ответственностью'),
                '425': __('Государственная организация (учреждение)'),
                '430': __('Коммунальная организация (учреждение)'),
                '910': __('Физическое лицо предприниматель'),
                '995': __('Другие организационно правовые формы'),
            }, form);
        }
    }
}
</script>