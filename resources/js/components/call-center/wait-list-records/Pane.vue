<template>
    <div>
        <section class="grey filter">
            <record-filter
                ref="filter"
                :start-collapsed="!displayFilter"
                :initial-state="filters"
                @changed="changeFilters"
                @cleared="clearFilters" />
        </section>
        <section class="grey-cap">
            <sticky-footer>
                <record-list
                    ref="table"
                    :filters="filters"
                    @selection-changed="setActiveItem"
                    @header-filter-updated="syncFilters" />
                <div slot="footer">
                    <el-button
                        v-if="$canUpdate('wait-list-record')"
                        :disabled="activeItem === null || !$canManage('wait-list-record.update', [activeItem.clinic_id])"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$canProcessCalls()"
                        :disabled="activeItem === null"
                        @click="voipSelectContact">
                        {{ __('Задать пациента для звонка') }}
                    </el-button>
                    <form-button
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportExcel(__('База ожидания'))" />
                </div>
            </sticky-footer>
        </section>
    </div>
</template>

<script>
import RecordFilter from './Filter.vue';
import RecordList from './List.vue';
import ManageMixin from '@/mixins/manage';
import FormEdit from './Edit.vue';
import SelectContactMixin from '../mixins/select-contact';
import ExportXLSXMixin from '@/mixins/export-xlsx-list';
import WaitListRecordsRepository from '@/repositories/wait-list-record';
import * as waitListRecordsGenerator from '@/components/call-center/wait-list-records/generators/records';

export default {
    mixins: [
        ManageMixin,
        SelectContactMixin,
        ExportXLSXMixin,
    ],
    components: {
        RecordFilter,
        RecordList,
    },
    data() {
        return {
            reportRepository: new WaitListRecordsRepository(),
            fileGenerator: waitListRecordsGenerator,
        }
    },
    methods: {
        getFilterUid() {
            return 'call-center-wait-list-records';
        },
        getModalOptions() {
            return {
                editForm: FormEdit,
                editHeader: __('Изменить заявку'),
                width: '750px',
            };
        },
        getMessages() {
            return {
                updated: __('Заявка было успешно обновлена'),
            };
        },
        getDefaultFilters() {
            return {
                period_from: this.$moment().format('YYYY-MM-DD'),
                period_to: this.$moment().format('YYYY-MM-DD'),
                ...(this.$isAccessLimited('wait-list-record') 
                    ? {clinic: this.getLoggedUserClinics()} 
                    : {}
                ),
            };
        },
        voipSelectContact() {
            if (this.activeItem.patient !== null) {
                this.selectPatientContact(this.activeItem.patient);
            } else {
                this.selectUnknownContact(this.activeItem.phone_number, this.activeItem.name);
            }
        },
    },
}
</script>
