<template>
    <div class="ehealth-address-widget">
        <el-row :gutter="20">
            <el-col :span="8">
                <form-select
                    ref="country"
                    :entity="val"
                    :disabled="countryLock"
                    property="country_code"
                    :options="countries"
                    :label="__('Страна')"
                    :required="isRequired"
                    :error-prefix="errorPrefix"
                    @changed="countrySelected" />
                <form-select
                    v-if="isUkraine"
                    ref="settlement"
                    :entity="val"
                    property="settlement_id"
                    :repository="settlements"
                    :debounce="1000"
                    :min-query-len="3"
                    :label="__('Населенный пункт')"
                    :required="isRequired"
                    :error-prefix="errorPrefix"
                    @changed="settlementSelected" />
            </el-col>
            <el-col
                v-if="isUkraine"
                :span="16">
                <el-row :gutter="20">
                    <el-col :span="12">
                        <form-select
                            :entity="val"
                            property="region"
                            :clearable="true"
                            :repository="regions"
                            :debounce="1000"
                            :min-query-len="3"
                            :label="__('Область')"
                            :required="isRequired"
                            :error-prefix="errorPrefix"
                            @changed="regionSelected" />
                        <form-select
                            ref="street"
                            :entity="val"
                            property="street_id"
                            :repository="streets"
                            :debounce="1000"
                            :min-query-len="3"
                            :label="__('Улица')"
                            :required="streetAddressRequired"
                            :error-prefix="errorPrefix"
                            @changed="streetSelected" />
                    </el-col>
                    <el-col :span="12">
                        <form-select
                            :entity="val"
                            property="district"
                            :clearable="true"
                            :repository="districts"
                            :debounce="1000"
                            :min-query-len="3"
                            :label="__('Район')"
                            :error-prefix="errorPrefix"
                            :required="false"
                            @changed="districtSelected" />
                        <el-row :gutter="20">
                            <el-col :span="14">
                                <div class="form-input-group">
                                    <form-input
                                        :entity="val"
                                        property="building"
                                        :label="__('№ стр.')"
                                        :required="streetAddressRequired"
                                        :error-prefix="errorPrefix"
                                        @changed="buildingChanged" />
                                    <form-input
                                        v-if="showApartment"
                                        :entity="val"
                                        property="apartment"
                                        :label="__('№ кв.')"
                                        :required="streetAddressRequired"
                                        :error-prefix="errorPrefix"
                                        @changed="apartmentChanged" />
                                </div>
                            </el-col>
                            <el-col :span="10">
                                <form-input
                                    :entity="val"
                                    property="zip"
                                    :label="__('Индекс')"
                                    :required="zipRequired"
                                    :error-prefix="errorPrefix"
                                    @changed="zipChanged" />
                            </el-col>
                        </el-row>
                    </el-col>
                </el-row>
            </el-col>
            <el-col
                v-else
                :span="16">
                <form-input
                    :entity="val"
                    property="address"
                    :label="__('Адрес')"
                    :required="true"
                    :error-prefix="errorPrefix"
                    @changed="addressChanged" />
            </el-col>
        </el-row>
    </div>
</template>

<script>
import CountryRepository from '@/repositories/country';
import ProxyRepository from '@/repositories/proxy-repository';
import ehealth from '@/services/ehealth';
import EhealthAddress from '@/models/ehealth/address';
import EhealthAddressMixin from '@/mixins/ehealth-address';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        EhealthAddressMixin,
    ],
    props: {
        value: Object,
        errorPrefix: {
            type: String,
            default: ''
        },
        isRequired: {
            type: Boolean,
            default: true,
        },
        streetAddressRequired: {
            type: Boolean,
            default: false,
        },
        zipRequired: {
            type: Boolean,
            default: false,
        },
        showApartment: {
            type: Boolean,
            default: true,
        },
        countryLock: {
            type: Boolean,
            default: false,
        },
    },
    computed: {
        isUkraine() {
            return this.val.country_code === CONSTANTS.COUNTRY_CODE.UA;
        },
    },
    data() {
        return {
            val: (this.value instanceof EhealthAddress) ? this.value : new EhealthAddress(),
            countries: [],
            regions: new ProxyRepository(({filters, limit}) => {
                if (filters.id !== undefined) {
                    return this.createIdOptions(filters.id[0]);
                }
                return this.searchRegions(filters.query, limit);
            }),
            districts: new ProxyRepository(({filters, limit}) => {
                if (filters.id !== undefined) {
                    return this.createIdOptions(filters.id[0]);
                }
                return this.searchDistricts(filters.query, limit);
            }),
            settlements: new ProxyRepository(({filters, limit}) => {
                if (filters.id !== undefined) {
                    return this.createIdOptions(filters.id[0], {
                        id: this.val.settlement_id,
                        name: this.val.settlement,
                        type: this.val.settlement_type,
                        value: this.formatSettlementName(this.val.settlement, this.val.settlement_type),
                    });
                }
                return this.searchSettlements(filters.query, limit);
            }),
            streets: new ProxyRepository(({filters, limit}) => {
                if (filters.id !== undefined) {
                    return this.createIdOptions(filters.id[0], {
                        id: this.val.street_id,
                        name: this.val.street,
                        type: this.val.street_type,
                        value: this.formatStreetName(this.val.street, this.val.street_type),
                    });
                }
                return this.searchStreets(filters.query, limit);
            }),
        };
    },
    mounted() {
        let repo = new CountryRepository();
        repo.fetchList().then((list) => {
            this.countries = list.map(item => ({
                id: item.code,
                value: item.value,
                id_value: item.id,
            }));
        });
    },
    methods: {
        searchRegions(query, limit) {
            return ehealth.getRegions(query, limit)
                .then((list) => {
                    return list.map(item => ({
                        id: item.name,
                        value: item.name,
                    }));
                });
        },
        searchDistricts(query, limit) {
            return ehealth.getDistricts(this.val.region, query, limit)
                .then((list) => {
                    return list.map(item => ({
                        id: item.name,
                        value: item.name,
                    }));
                });
        },
        searchSettlements(query, limit) {
            return ehealth.getSettlements(this.val.region, this.val.district, query, limit)
                .then((list) => {
                    return list.map(item => ({
                        id: item.id,
                        value: this.formatSettlementName(item.name, item.type),
                        name: item.name,
                        type: item.type,
                    }));
                });
        },
        searchStreets(query, limit) {
            if (this.val.settlement_id) {
                return ehealth.getStreets(this.val.settlement_id, query, limit)
                    .then((list) => {
                        return list.map(item => ({
                            id: item.id,
                            name: item.name,
                            type: item.type,
                            value: this.formatStreetName(item.name, item.type),
                        }));
                    });
            }
            return Promise.resolve([]);
        },
        countrySelected(val) {
            let options = this.$refs.country.getAvailableOptions();
            let option = _.find(options, opt => opt.id === val);
            if (option) {
                this.val.country = option.value;
                this.val.country_id = option.id_value;
            } else {
                this.val.country = null;
                this.val.country_id = null;
            }
            this.clearRegion();
            this.renewAddress();
        },
        clearRegion() {
            this.val.region = null;
            this.clearDistrict();
        },
        regionSelected(val) {
            this.clearDistrict();
            this.renewAddress();
        },
        clearDistrict() {
            this.val.district = null;
            this.clearSettlement();
        },
        districtSelected(val) {
            this.clearSettlement();
            this.renewAddress();
        },
        clearSettlement() {
            this.val.settlement = null;
            this.val.settlement_id = null;
            this.val.settlement_type = null;
            this.clearStreet();
        },
        settlementSelected(val) {
            let options = this.$refs.settlement.getAvailableOptions();
            let option = _.find(options, opt => opt.id === val);
            if (option) {
                this.val.settlement = option.name;
                this.val.settlement_type = option.type;
                this.clearStreet();
            } else {
                this.clearSettlement();
            }
            this.renewAddress();
        },
        clearStreet() {
            this.val.street = null;
            this.val.street_id = null;
            this.val.street_type = null;
        },
        streetSelected(val) {
            let options = this.$refs.street.getAvailableOptions();
            let option = _.find(options, opt => opt.id === val);
            if (option) {
                this.val.street = option.name;
                this.val.street_type = option.type;
            } else {
                this.clearStreet();
            }
            this.renewAddress();
        },
        buildingChanged() {
            this.renewAddress();
        },
        apartmentChanged() {
            this.renewAddress();
        },
        zipChanged() {
            this.renewAddress();
        },
        addressChanged() {
            this.$emit('input', this.val);
        },
        renewAddress() {
            if (this.isUkraine) {
                let address = this.composeAddress();
                if (address !== this.val.address) {
                    this.val.address = address;
                    this.$emit('input', this.val);
                }
            }
        },
        composeAddress() {
            let address = [];
            let v = this.val;

            if (_.isVoid(v.country) ||
                _.isVoid(v.region) ||
                _.isVoid(v.settlement)) {
                return null;
            }

            if (_.isFilled(v.zip)) {
                address.push(v.zip);
            }

            address.push(v.country);
            address.push(this.formatRegionName(v.region));

            if (_.isFilled(v.district)) {
                address.push(this.formatDistrictName(v.district));
            }

            address.push(this.formatSettlementName(v.settlement, v.settlement_type));

            if (_.isFilled(v.street)) {
                address.push(this.formatStreetName(v.street, v.street_type));

                if (_.isFilled(v.building)) {
                    address.push(this.formatBuildingNumber(v.building));
                }

                if (_.isFilled(v.apartment)) {
                    address.push(this.formatApartmentNumber(v.apartment));
                }
            }

            return address.join(', ');
        },
        createIdOptions(id, data = null) {
            if (data === null) {
                data = {id, value: id}
            } else if (data.id !== id) {
                return Promise.resolve([]);
            }
            return Promise.resolve([{
                ...data,
            }]);
        },
    }
}
</script>
