<template>
    <div :class="heightClass" class="page">
        <section class="grey pt-10">
            <day-sheet-filter
                ref="filter"
                :initial-state="filters"
                @changed="changeFilters"/>
        </section>
        <section
            v-if="showList"
            class="grey-cap pt-0 flex-content"
            style="height: 360px;">
            <doctor-list
                ref="table"
                :filters="filters"
                @selection-changed="selectionChanged"
                @header-filter-updated="syncFilters"/>
        </section>
        <div v-else id="empty-data-wrapper">
            <div id="empty-data">
                <div id="empty-data-img"></div>
                <span>{{ __('Выберите клинику и период работы') }}</span>
            </div>
        </div>
        <el-row class="list-table-buttons">
            <div style="position: absolute;left: 20px;">
                <span class="input-label" v-show="selectedLength">
                    {{ __('Итого:') }} {{ selectedLength }}
                </span>
            </div>
            <el-button
                class="primary-btn-outline no-margin-t"
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                class="primary-btn no-margin-t"
                type="primary"
                :disabled="disableSelect"
                @click="selected">
                {{ __('Добавить выбранные') }}
            </el-button>
        </el-row>
    </div>
</template>

<script>
import DaySheetFilter from './search-doctor-daysheet/Filter.vue';
import DoctorList from './search-doctor-daysheet/List.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        DaySheetFilter,
        DoctorList,
    },
    props: {
        doctor: {
            type: [Number, String],
            default: null,
        },
        patient: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            showList: false,
            disableSelect: true,
            selectedLength: 0
        }
    },
    computed: {
        heightClass() {
            return 'select-daysheet-modal ' + (this.showList ? 'modal-appointment-height-filtered' : 'modal-appointment-height-init');
        }
    },
    methods: {
        getFilterUid() {
            return false;
        },
        getDefaultFilters() {
            let filters = {
                clinic: this.$store.state.user.clinics[0],
                surgery: this.$store.state.user.isDoctor || null,
            };
            if (this.patient && this.patient.clinics && this.patient.clinics.length === 1) {
                filters.clinic = this.patient.clinics[0];
            } else if (this.$store.state.processState.clinic) {
                filters.clinic = this.$store.state.processState.clinic;
            }
            return _.onlyFilled(filters);
        },
        cancel() {
            this.$emit('cancel');
        },
        changeFilters(filters) {
            this.showList = true;
            if (this.doctor) {
                filters.or = {
                    employees: this.doctor,
                    day_sheet_employee: this.doctor,
                }
            } else {
                delete filters.or;
            }
            this.filters = filters;
        },
        selectionChanged(rows) {
            this.disableSelect = !rows.length;
            this.selectedLength = rows.length;
        },
        selected() {
            this.$emit('selected', {
                params: this.$refs.table.getSelectedRows(),
                showAdjacent: this.$refs.filter.adjacentSpecialization,
            });
        },
    }
}
</script>
