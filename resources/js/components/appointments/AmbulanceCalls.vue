<template>
    <page
        :title="__('Журнал вызовов скорой помощи')"
        v-loading="loading"
        :element-loading-text="__('Генерация отчета...')"
        type="flex"
    >
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __("Фильтр") }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey filter">
                <ambulance-call-filter
                    ref="AmbulanceCallFilter"
                    :initial-state="filters"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable"
                />
            </section>
        </drawer>
        <section v-if="displayTable" class="grey-cap shrinkable">
            <ambulance-call-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @header-filter-updated="syncFilters"
                @loaded="refreshed"
            >
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$can('ambulance-calls.export')"
                        :text="__('Экспорт в Excel')"
                        icon="download"
                        @click="exportExcel(__('Вызовы скорой'))"
                    />
                    <form-button
                        v-if="$can('action-logs.access')"
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog"
                    />
                </div>
            </ambulance-call-list>
        </section>
    </page>
</template>

<script>
import AmbulanceCallFilter from "./ambulance-calls/Filter.vue";
import AmbulanceCallList from "./ambulance-calls/List.vue";
import ManageMixin from "@/mixins/manage";
import ExportXLSXMixin from "@/mixins/export-xlsx-list";
import AmbulanceCallLog from "@/components/action-log/appointment/AmbulanceCall";
import AmbulanceCallRepository from "@/repositories/appointment/ambulance-call";
import * as ambulanceCallGenerator from "./generators/ambulance-calls";
import AppointmentManagerMixin from '@/components/appointments/mixin/manager';

export default {
    mixins: [ManageMixin, ExportXLSXMixin],
    components: {
        AmbulanceCallFilter,
        AmbulanceCallList
    },
    data() {
        return {
            displayFilter: true,
            displayTable: false,
            reportRepository: new AmbulanceCallRepository({
                filters: { appointment_completed: true }
            }),
            loading: false,
            fileGenerator: ambulanceCallGenerator
        };
    },
    methods: {
        clearFiltersAndHideTable() {
            this.displayTable = false;
            this.clearFilters();
        },
        changeFiltersAndShowTable(filters) {
            this.changeFilters(filters);
            this.displayTable = true;
        },
        refresh() {
            if (this.displayTable) {
                this.getManageTable().refresh();
            }
        },
        getFilter() {
            return this.$refs.AmbulanceCallFilter;
        },
        showLog() {
            this.$modalComponent(
                AmbulanceCallLog,
                {
                    id: this.activeItem.id
                },
                {
                    close: dialog => {
                        dialog.close();
                    }
                },
                {
                    header: __("История изменения вызова скорой помощи"),
                    width: "900px",
                    customClass: "no-footer"
                }
            );
        }
    }
};
</script>
