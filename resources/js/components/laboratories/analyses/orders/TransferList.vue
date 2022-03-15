<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :initial-sort-order="initialSortOrder"
        :filters="filters"
        :scopes="scopes"
        :flex-height="true"
        @loaded="loaded"
        @header-filter-updated="syncFilters">
        <template
            slot-scope="props"
            slot="barcode">
            <div style="display: flex; justify-content: space-between">
                <a
                    href="#"
                    @click.prevent="showDetails(props.rowData)">
                    {{ props.rowData.barcode }}
                </a>
                <svg-icon
                        name="info-alt"
                        class="icon-tiny icon-grey"
                        :title="__('Операции')"
                        @click.stop="showLog(props.rowData.id)" />
            </div>
        </template>
       <template
            slot="status"
            slot-scope="props">
            <a v-if="props.rowData.status === 'new'" @click="send(props.rowData)">
                {{ __('Отправить') }}
            </a>
            <span v-else>{{ $handbook.getOption('transfer_sheet_status', props.rowData.status) }}</span>
       </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import TransferSheetRepository from '@/repositories/analysis/laboratory/transfer-sheet';
import ClinicRepository from '@/repositories/clinic';
import DateHeaderFilter from '@/components/general/table/DateHeaderFilter.vue';
import CONSTANTS from '@/constants';
import TransferListLog from "@/components/action-log/TransferList.vue";

export default {
    data() {
        return {
            repository: new TransferSheetRepository(),
            fields: [
                {
                    name: 'barcode',
                    title: __('Штрихкод отправки'),
                    width: '150px',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    width: '150px',
                    filter: 'transfer_sheet_status',
                    filterProps: {
                        multiple: true,
                    },
                    sortField: 'status',
                },
                {
                    name: 'created',
                    title: __('Сформирован'),
                    width: '200px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filterField: 'created_at',
                    filter: DateHeaderFilter,
                    sortField: 'created_at',
                },
                {
                    name: 'send_time',
                    title: __('Дата и время отправки'),
                    width: '200px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },
                    filterField: 'send_time',
                    filter: DateHeaderFilter,
                    sortField: 'send_time',
                },
                {
                    name: 'clinic_name',
                    title: __('Пункт отправитель'),
                    width: '200px',
                    filter: new ClinicRepository({
                        accessLimit: this.$isAccessLimited('laboratory-transfers'),
                    }),
                    filterField: 'clinic',
                    filterProps: {
                        multiple: true,
                    },
                },
                {
                    name: 'reciever_name',
                    title: __('Пункт получатель'),
                    width: '200px',
                    filter: true,
                    filterProps: {
                        searchModes: true,
                    },
                },
                {
                    name: 'sender_comment',
                    title: __('Примечание отправителя'),
                    width: '250px',
                },
                {
                    name: 'reciever_comment',
                    title: __('Примечание получателя'),
                    width: '250px',
                },
            ],
            initialSortOrder: [
                {field: 'created_at', direction: 'desc'},
            ],
            scopes: [
                'laboratory',
                'clinic',
                'orders',
                'items',
            ],
            filters: {
                clinic: this.$store.state.user.clinics,
            }
        }
    },
    methods: {
        showDetails(transfer) {
            this.$emit('show-details', transfer);
        },
        showLog(id) {
            this.$modalComponent(TransferListLog, {
                id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        send(row) {
            this.$confirm(__('Вы уверены, что хотите отправить заказ-наряд в лабораторию?'), () => {
                row.changeStatus(CONSTANTS.TRANFER_SHEET_STATUES.TRANSPORTING).then(() => {
                    this.$info(__('Заказ-наряд отправлен в лабораторию'));
                }).catch((e) => {
                    console.log(e);
                    this.$warning(__('Что-то пошло не так'));
                });
            });
        },
        loaded() {
            this.$emit('loaded');
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
}
</script>
