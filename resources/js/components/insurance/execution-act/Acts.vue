<template>
    <div v-loading="loading">
        <section class="grey filter">
            <insurance-act-filter
                ref="filter"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <section class="grey-cap p-20">
            <act-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$canCreate('insurance-acts') || $canUpdate('insurance-acts')"
                        :disabled="activeItem === null"
                        :text="__('Выгрузить')"
                        icon="download"
                        @click="exportAct" />
                    <form-button
                        v-if="$canCreate('insurance-acts') || $canUpdate('insurance-acts')"
                        :disabled="activeItem === null"
                        :text="__('Комментарий')"
                        icon="menu-appointment-diagnostic"
                        @click="edit" />
                    <form-button
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog" />
                    <form-button
                        v-if="$canUpdate('insurance-acts')"
                        :disabled="canAddPayment(activeItem)"
                        :text="__('Добавить оплату')"
                        icon="plus"
                        @click="addPayment" />
                    <form-button
                        v-if="$canUpdate('insurance-acts')"
                        :disabled="canChangeStatus(activeItem)"
                        :text="__('Закрыть акт')"
                        icon="edit"
                        @click="closeAct" />
                </div>
            </act-list>
        </section>
    </div>
</template>
<script>
import ManageMixin from '@/mixins/manage';
import ExportActMixin from './mixin/act-requisites';
import InsuranceActFilter from './Filter.vue';
import ActList from './ActList.vue';
import AppointmentServiceRepository from '@/repositories/appointment/service';
import FileSaver from 'file-saver';
import actGenerator from './generator/act';
import CONSTANTS from '@/constants';
import ActLog from '@/components/action-log/insurance-company/Act.vue';
import FormEdit from './FormEdit.vue';
import addPayment from './AddPayment.vue';

export default {
    mixins: [
        ManageMixin,
        ExportActMixin,
    ],
    components: {
        InsuranceActFilter,
        ActList,
    },
    data() {
        return {
            loading: false,
        }
    },
    methods: {
        getFilterUid() {
            return 'insurance-executive-act-list';
        },
        getModalOptions() {
            return {
                editForm: FormEdit,
                editHeader: __('Изменить акт'),
                width: '500px',
            };
        },
        getMessages() {
            return {
                updated: __('Акт был успешно обновлен'),
            };
        },
        exportAct() {
            this.loading = true;
            let rows = [];
            let makeAct = async () => {
                rows = await this.getActServices();
                if (rows.length === 0) {
                    this.loading = false;
                    return this.$error(__('Данные для формирования акта отсутствуют'));
                }

                let requisites = await this.getRequisites(this.activeItem.clinic_id, this.activeItem.insurance_company_id);
                requisites.insuranceAct = this.activeItem;

                actGenerator(rows, requisites).then(({book, amount}) => {
                    book.xlsx.writeBuffer().then((buffer) => {
                        FileSaver.saveAs(new Blob([buffer]), __('Акт выполненных работ (страховые)') + '.xlsx');
                        this.loading = false;
                        this.activeItem.printed();
                    }).catch((err) => {
                        console.error(err);
                        this.$error(__('Не удалось сохранить файл'));
                        this.loading = false;
                    });
                });
            };
            makeAct();
        },
        getActServices() {
            let repo = new AppointmentServiceRepository();
            return repo.fetchInsuranceExportList({insurance_act: this.activeItem.id}, null, null, 1, 1000)
                .then((response) => {
                    return Promise.resolve(response.rows);
                });
        },
        showLog() {
            this.$modalComponent(ActLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История печати акта'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        addPayment() {
            this.$modalComponent(addPayment, {
                act: this.activeItem,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
                added: (dialog) => {
                    dialog.close();
                    this.refresh();
                },
            }, {
                header: __('Внести оплату'),
                width: '400px',
            });
        },
        closeAct() {
            this.$confirm(
                __('Вы уверены что все платежи были разнесены? Данное действие нельзя отменить.'),
                () => {
                    this.loading = true;
                    this.activeItem.status = CONSTANTS.INSURANCE_ACT.STATUSES.PAYED;
                    this.activeItem.payment_status = CONSTANTS.INSURANCE_ACT.STATUSES.PAYED;
                    this.activeItem.save().then(() => {
                        this.refresh();
                        this.loading = false;
                    });
                },
                {
                    confirmBtnText: __('Ок'),
                    cancelBtnText: __('Отменить'),
                }
            );
        },
        canAddPayment(row) {
            return (row === null
            || row.status === CONSTANTS.INSURANCE_ACT.STATUSES.PAYED
            || row.payment_status === null
            || row.payment_status === CONSTANTS.INSURANCE_ACT.STATUSES.PAYED
            || row.status === CONSTANTS.INSURANCE_ACT.STATUSES.PARTLY)
            ? true : false;
        },
        canChangeStatus(row) {
            return  (row === null || row.status != CONSTANTS.INSURANCE_ACT.STATUSES.PARTLY)? true : false;
        }
   },
}
</script>
