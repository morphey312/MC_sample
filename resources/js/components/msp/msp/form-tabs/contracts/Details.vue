<template>
    <content-placeholder 
        v-if="details === null" />
    <div v-else
        class="contract-container">
        <div class="sections-wrapper">
            <section class="grey">
                <div class="manage-table">
                    <table class="vuetable unstackable celled table">
                        <tr>
                            <th width="20%">{{ __('Статус') }}</th>
                            <td>
                                {{ $handbook.getOption('ehealth_contract_status', details.status) }}
                            </td>
                        </tr>
                        <tr v-if="details.status_reason">
                            <th>{{ __('Комментарий НСЗУ') }}</th>
                            <td>
                                {{ details.status_reason }}
                            </td>
                        </tr>
                        <tr v-if="details.reason">
                            <th>{{ __('Комментарий НСЗУ') }}</th>
                            <td>
                                {{ details.reason }}
                            </td>
                        </tr>
                        <tr v-if="details.contractor_legal_entity && details.contractor_owner">
                            <th>{{ __('Сторона 1') }}</th>
                            <td class="no-ellipsis">
                                {{ details.contractor_legal_entity.name }}<br />
                                <b>{{ __('ЕГРПОУ') }}:</b> {{ details.contractor_legal_entity.edrpou }} <br />
                                <b>{{ __('Адрес') }}:</b> {{ formatAddresses(details.contractor_legal_entity.addresses) }} <br />
                                <b>{{ __('Подписант') }}:</b> {{ fullName(details.contractor_owner.party) }} <br />
                                <b>{{ __('Основание') }}:</b> {{ details.contractor_base }} <br />
                            </td>
                        </tr>
                        <tr v-if="details.nhs_legal_entity && details.nhs_signer">
                            <th>{{ __('Сторона 2') }}</th>
                            <td class="no-ellipsis">
                                {{ details.nhs_legal_entity.name }}<br />
                                <b>{{ __('ЕГРПОУ') }}:</b> {{ details.nhs_legal_entity.edrpou }} <br />
                                <b>{{ __('Адрес') }}:</b> {{ formatAddresses(details.nhs_legal_entity.addresses) }} <br />
                                <b>{{ __('Подписант') }}:</b> {{ fullName(details.nhs_signer.party) }} <br />
                                <b>{{ __('Основание') }}:</b> {{ details.nhs_signer_base }} <br />
                            </td>
                        </tr>
                    </table>
                </div>
            </section>
            <section v-if="details.printout_content">
                <iframe 
                    src="about:blank"
                    ref="printout"
                    width="100%" 
                    height="500"
                    border="0"
                    style="width: 100%; height: 500px; border: none;">
                </iframe>
            </section>
        </div>
        <div 
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Назад') }}
            </el-button>
            <el-button 
                v-if="printout"
                @click="print">
                {{ __('Печать') }}
            </el-button>
            <el-button 
                v-if="isWaitApprove"
                :disabled="loading"
                type="primary"
                @click="approve">
                {{ __('Утвердить') }}
            </el-button>
            <el-button 
                v-if="isWaitSign"
                :disabled="loading"
                type="primary"
                @click="sign">
                {{ __('Подписать') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import ehealth from '@/services/ehealth';
import EhealthAddressMixin from '@/mixins/ehealth-address';
import CONSTANT from '@/constants';
import DigitalSignMixin from '@/mixins/digital-sign';

export default {
    mixins: [
        EhealthAddressMixin,
        DigitalSignMixin,
    ],
    props: {
        contract: Object,
    },
    computed: {
        isWaitApprove() {
            return this.details && this.details.status === CONSTANT.EHEALTH.CONTRACT_STATUS.APPROVED;
        },
        isWaitSign() {
            return this.details && this.details.status === CONSTANT.EHEALTH.CONTRACT_STATUS.NHS_SIGNED;
        },
    },
    data() {
        return {
            details: null,
            printout: null,
            loading: false,
            urls: null,
        };
    },
    mounted() {
        let promise = ehealth.getContractRequestDetails(this.contract.type, this.contract.ehealth_request_id);

        promise.then((result) => {
            this.details = result.data;
            this.urls = result.urgent.documents;
            this.$nextTick(() => {
                if (_.isFilled(this.details.printout_content)) {
                    this.printout = this.$refs.printout.contentWindow;
                    this.printout.document.open();
                    this.printout.document.write(this.details.printout_content);
                    this.printout.document.close();
                }
            });
        });
    },
    methods: {
        cancel() {
            this.$emit('close');
        },
        print() {
            this.printout.print();
        },
        fullName(party) {
            return [
                party.last_name,
                party.first_name,
                party.second_name,
            ].join(' ');
        },
        formatAddresses(addrs) {
            return addrs.map(a => this.formatAddress(a)).join('; ');
        },
        formatAddress(addr) {
            let parts = [];
            if (_.isFilled(addr.zip)) {
                parts.push(addr.zip);
            }
            if (_.isFilled(addr.area)) {
                parts.push(this.formatRegionName(addr.area));
            }
            if (_.isFilled(addr.settlement) && (_.isVoid(addr.area) || addr.area.substr(0, 2) !== 'М.')) {
                parts.push(this.formatSettlementName(addr.settlement, addr.settlement_type));
            }
            if (_.isFilled(addr.street)) {
                let decor = this.formatStreetName('', addr.street_type);
                if (addr.street.indexOf(decor) !== -1) {
                    parts.push(addr.street);
                } else {
                    parts.push(this.formatStreetName(addr.street, addr.street_type));
                }
                if (_.isFilled(addr.building)) {
                    parts.push(this.formatBuildingNumber(addr.building));
                }
                if (_.isFilled(addr.apartment)) {
                    parts.push(this.formatApartmentNumber(addr.apartment));
                }
            }
            return parts.join(', ');
        },
        approve() {
            this.loading = true;
            this.contract.approve().then(() => {
                this.loading = false;
                this.$info(__('Запрос на утверждение был отправлен'));
                this.$emit('close');
            }).catch(() => {
                this.loading = false;
                this.$error(__('При утверждении договора возникла ошибка'));
            });
        },
        sign() {
            let signed = _.find(this.urls, (d) => d.type === 'SIGNED_CONTENT');
            if (signed === undefined) {
                this.$error(__('Отсутствуют данные для подписи'));
                return;
            }

            this.loading = true;
            ehealth.getDocumentContent(signed.url).then((content) => {
                this.loading = false;
                this.signData(content, (signed) => {
                    this.loading = true;
                    this.contract.sign(signed).then(() => {
                        this.loading = false;
                        this.$info(__('Подписаный документ был отправлен'));
                        this.$emit('close');
                    }).catch(() => {
                        this.loading = false;
                        this.$error(__('Не удалось отправить подписаный документ'));
                    });
                }, {
                    checkbox: __('Накладывая свою электронную подпись/квалифицированную электронную подпись, я осознаю наступление определенных прав и обязательств, понял текст договора.'),
                });
            }).catch(() => {
                this.loading = false;
                this.$error(__('Не удалось загрузить документ для подписи'));
            });
        },
    }
}
</script>