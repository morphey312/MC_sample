<template>
    <model-form :model="model" v-loading="loading">
    <manage-table
        ref="table"
        class="price-grid"
        :fields="fields"
        :filters="filters"
        :row-class="rowClass"
        :repository="repository"
        :scopes="scopes"
        @header-filter-updated="syncFilters"
        @loaded="loaded">
        <template slot="recommended_cost" slot-scope="props">
            <form-input
                :entity="props.rowData"
                :disabled="isClosed(props.rowData)"
                property="recommended_cost"
                :formatter="$formatter.numberFormat"
                :validator="isValidPrice"
                css-class="mb-0"
                @changed="priceChanged(props.rowData)"
                :required="true" />
        </template>
        <template slot="clinics" slot-scope="props">
            <form-select
                :options="clinics"
                :entity="props.rowData"
                property="clinics"
                control-size="mini"
                css-class="mb-0"
                :disabled="true"
                :multiple="true"/>
        </template>
        <template
            slot="remove"
            slot-scope="props">
            <span @click="remove(props.rowData)"  v-if="inWorkStatus">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
        <div class="buttons text-left" slot="footer-top">
            <el-button
                @click="showAppointmentLog">
                {{ __('Операции') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="disabledRoom"
                @click="showChat">
                {{ __('Чат') }}
            </el-button>
            <el-button
                :disabled="prices.length === 0 || !inWorkStatus"
                @click="confirm">
                {{ __('Подтвердить') }}
            </el-button>
        </div>
        </manage-table>
    </model-form>
</template>

<script>
import EditMixin from '@/mixins/generic-edit';
import PriceRepository from "@/repositories/price-agreement-act/price";
import ProxyRepository from '@/repositories/proxy-repository';
import ClinicRepository from "@/repositories/clinic";
import Room from "@/models/chat/room";
import BatchRequest from '@/services/batch-request';
import Chat from './Chat.vue'
import ChatHeaderSettings from './ChatHeaderSettings.vue'
import PriceAgreementAct from '@/components/action-log/PriceAgreementAct.vue';
import SpecializationRepository from "@/repositories/specialization";
import CONSTANTS from "@/constants";

export default {
    mixins: [
        EditMixin,
    ],
    props: {
        item: Object,
    },
    mounted() {
        this.getClinics();
        this.getUserRooms();
    },
    data() {
        return {
            loading: true,
            disabledRoom: true,
            clinics: [],
            prices: [],
            model: this.item.clone(),
            batchRequest: new BatchRequest('api/v1/price-agreement-act/price/batch'),
            repository: new ProxyRepository(({filters, sort, scopes, page, limit}) => {
                let repo = new PriceRepository();
                return repo.fetch({act: this.item.id, ...filters}, null, scopes, page, limit).then((result) => {
                    this.loading = false;
                    return Promise.resolve({
                        rows: result.rows,
                        pagination: result.pagination
                    });
                });
            }),
            fields: [
                {
                    name: '__checkbox',
                    width: '2%',
                },
                ...((this.isAnalysis()) ? [{
                        name: 'service_name',
                        title: __('Название анализа'),
                        width: '30%',
                        filter: true,
                        filterField: 'service_name',
                    }] : [{
                        name: 'name',
                        title: __('Название услуги'),
                        width: '30%',
                        filter: true,
                        filterField: 'service_name',

                    }]),
                 ...((this.isAnalysis()) ? [{
                    name: 'laboratory_name',
                    title: __('Лаборатория'),
                    width: '5%',
                    filter: true,
                    filterField: 'laboratory_name',
                 }] : [{
                    name: 'specialization_name',
                    title: __('Специализация'),
                    width: '5%',
                    filter: new SpecializationRepository(),
                    filterField: 'specialization',
                 }]),
                 ...((this.isAnalysis()) ? [{
                    name: 'laboratory_code',
                    title: __('Код лаб.'),
                    width: '5%',
                    filter: true,
                    filterField: 'laboratory_code',
                }]: []),
                ...((this.isAnalysis()) ? [{
                    name: 'clinics_code',
                    title: __('Код клиники'),
                    width: '10%',
                    filter: true,
                    filterField: 'clinics_code',
                    formatter: (val) => {
                        return this.$formatter.listFormat(val, 'code');
                    }
                }]: []),
                {
                    name: 'clinics',
                    title: __('Клиники'),
                    width: '12%',
                    filter: new ClinicRepository(),
                    filterField: 'clinic',

                },
                {
                    name: 'cost',
                    title: __('Стоимость'),
                    width: '5%',
                },
                {
                    name: 'recommended_cost',
                    title: __('Стоимость рекомендованная'),
                    width: '5%',
                },
                {
                    name: 'price_date_from',
                    title: __('Дата начала тарифа'),
                    width: '10%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                {
                    name: 'price_date_to',
                    title: __('Дата закрытия тарифа'),
                    width: '10%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                {
                    name: 'remove',
                    title: '',
                    width: "35px",
                    dataClass: 'no-ellipsis no-dash',
                }
            ],
            filters: {},
            scopes: [
                'clinics',
            ],
        };
    },
    computed: {
        inWorkStatus() {
            return this.item.status === CONSTANTS.PRICE_AGREEMENT_ACT.STATUSES.IN_WORK;
        },
    },
    methods: {
        remove(item) {
            this.$confirm(__('Вы уверены, что хотите удалить эти записи?'), () => {
                this.loading = true;
                item.delete().then(() => {
                    this.getManageTable().refresh();
                    this.$emit('refresh');
                    this.loading = false;
                });
            });
        },
        getManageTable() {
            return this.$refs.table;
        },
        rowClass(row) {
            if (this.isClinicAdd(row)) {
                return ['new-clinic', 'success'];
            }
            if (this.isNewPrice(row)) {
                return ['new-price', 'success'];
            }
            if (this.isClosed(row)) {
                return ['changed', 'closed', 'success'];
            }
            if (this.recommendedCostChanged(row)) {
                return ['changed', 'warning'];
            }
            return ''
        },
        isClosed(price) {
            return price.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CLOSE_PRICE;
        },
        recommendedCostChanged(price) {
            return price.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.CHANGE_COST;
        },
        isClinicAdd(price) {
            return price.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.ADD_CLINIC;
        },
        isNewPrice(price) {
            return price.change_type === CONSTANTS.PRICE_AGREEMENT_ACT.PRICE.CHANGE_TYPE.NEW_PRICE;
        },
        confirm() {
            if (_.isEmpty(this.prices)) {
                return;
            }
            this.loading = true;
            this.batchRequest.reset();
            this.prices.forEach((row) => {
                this.batchRequest.update(row);
            });

            this.batchRequest.submit().then((result) => {
                this.loading = false;
                this.$emit('cancel');
             });

        },
        isAnalysis() {
            return this.item.type === CONSTANTS.PRICE.SERVICE_TYPE.ANALYSIS;
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        isValidPrice(val) {
            return !isNaN(Number(val)) && val >= 0;
        },
        getClinics() {
            let clinicsRepository = new ClinicRepository();
            clinicsRepository.fetchList().then((response) => {
                this.clinics = response;
            });
        },
        getUserRooms() {
            let room = new Room({id: this.item.id});
            room.fetch(['employees']).then((response) => {
                let haveAccess = response.response.data.employees.find(item => {
                    return item.id === this.$store.state.user.employee_id;
                });
                if (haveAccess) {
                    this.disabledRoom = false;
                }
            });
        },
        getVuetable() {
            return this.$refs.table.$refs.table;
        },
        priceChanged(row) {
            let price = this.prices.find(item => { return item.id === row.id});
            if (price) {
                price = row;
            } else {
                this.prices.push(row);
            }
        },
        showChat() {
            this.$modalComponent(Chat, {
                room_id: this.item.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Обсуждение'),
                width: '600px',
                customClass: 'no-footer chat-wrapper',
                headerAddon: {
                    component: ChatHeaderSettings,
                    props: {
                        chat_id: this.item.id,
                    },
                    eventListeners: {
                        showSettings: (dialog, users) => {
                            dialog.getTopComponent().settings();
                        },
                    }
                    },
            });
        },

        showAppointmentLog(){
            this.$modalComponent(PriceAgreementAct, {
                id: this.item.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения акта для согласования'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    }
};
</script>
