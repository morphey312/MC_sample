<template>
    <prices-list
        :filters="filters"
        :premissions="premissions"
        ref="table"
        @selection-changed="setActiveItem"
        @loaded="refreshed"
        @header-filter-updated="updateFilters">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$canCreate(premissions)"
                @click="create">
                {{ __('Добавить тариф') }}
            </el-button>
            <el-button
                v-if="$canUpdate(premissions)"
                :disabled="activeItem === null || !$canManage(premissions + '.update', activeItem.clinics)"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$canUpdate(premissions)"
                :disabled="activeItem === null || activeItem.date_to !== null || !$canManage(premissions + '.update', activeItem.clinics)"
                @click="selectCloseDate">
                {{ __('Закрыть') }}
                <el-date-picker
                    v-if="activeItem"
                    ref="datepicker"
                    v-model="closeDate"
                    :picker-options="pickerOptions"
                    type="date"
                    value-format="yyyy-MM-dd"
                    @change="closePrice">
                </el-date-picker>
            </el-button>
            <el-button
                v-if="$canDelete(premissions)"
                :disabled="activeItem === null || !$canManage(premissions + '.delete', activeItem.clinics)"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
            <el-button
                v-if="$can('action-logs.access')"
                @click="showLog">
                {{ __('Операции с тарифами') }}
            </el-button>
        </div>
    </prices-list>
</template>

<script>
import CreatePrice from './Create.vue';
import EditPrice from './Edit.vue';
import PricesList from './List';
import PriceRepository from '@/repositories/price';
import PriceSetRepository from '@/repositories/price/set';
import ManageMixin from '@/mixins/manage';
import PriceLog from '@/components/action-log/Price.vue';
import Analysis from '@/models/analysis';
import Service from '@/models/service';
import CONSTANTS from '@/constants';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        PricesList,
    },
    props: {
        subject: Object,
        baseFilters: Object,
        premissions: String,
    },
    data() {
        return {
            filters: this.baseFilters,
            closeDate: null,
            pickerOptions: {
                disabledDate: this.checkDisabledDate,
            },
            priceSets: [],
        };
    },
    mounted() {
        this.getPriceSets();
    },
    methods: {
        getDefaultFilters() {
            return {
                clinic: [],
            };
        },
        updateFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        getModalOptions() {
            return {
                createForm: CreatePrice,
                createProps: {
                    subject: this.subject,
                    setId: this.getSetId(),
                    priceSets: this.priceSets,
                    premissions: this.premissions,
                },
                editForm: EditPrice,
                editProps: {
                    subject: this.subject,
                    premissions: this.premissions,
                    priceSets: this.priceSets,
                },
                createHeader: __('Добавление тарифа'),
                editHeader: __('Изменение тарифа'),
                width: '750px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот тариф?'),
                deleted: __('Тариф был успешно удален'),
                created: __('Тариф был успешно добавлен'),
                updated: __('Тариф был успешно обновлен'),
            };
        },
        selectCloseDate() {
            this.closeDate = null;
            this.$refs.datepicker.focus();
        },
        checkDisabledDate (date) {
            return this.$moment(date).isBefore(this.activeItem.$.date_from);
        },
        closePrice() {
            this.activeItem.date_to = this.closeDate;
            this.activeItem.save().then((response) => {
                this.$info(__('Тариф успешно закрыт'));
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        showLog() {
            this.$modalComponent(PriceLog, {
                id: this.subject.id,
                category:  this.getType(this.subject),
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения тарифов'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        getType(subject) {
            if(this.subject instanceof Service) {
                return CONSTANTS.PRICE.SERVICE_TYPE.SERVICES;
            }
            if(this.subject instanceof Analysis) {
                return CONSTANTS.PRICE.SERVICE_TYPE.ANALYSIS;
            }
            return null;
        },
        getPriceSets() {
            let set = new PriceSetRepository();
            set.fetchList({type: [CONSTANTS.PRICE.SET_TYPE.BASE, CONSTANTS.PRICE.SET_TYPE.CERTIFICATE]}).then(response => {
                let handbookTypes = this.$handbook.getOptions('price_set');
                this.priceSets = response.map(s => {
                    let set = handbookTypes.find(type => type.id === s.type);
                    return {
                        id: s.id,
                        type: s.type,
                        value: set ? set.value : null,
                    };
                });
            });
        },
        getSetId() {
            let set = this.priceSets.find(s => s.type === this.filters.set_type);
            return set ? set.id : null;
        },
    },
    watch: {
        baseFilters(val) {
            this.filters = {...val};
            this.activeItem = null;
        }
    }
};
</script>
