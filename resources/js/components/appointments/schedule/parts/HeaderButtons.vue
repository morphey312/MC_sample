<template>
    <div class="buttons">
        <span>
            <el-switch
                v-model="scheduleColumnsCollapsed"
                active-color="#B7B308"
                inactive-color="#BDBDBD"
                @change="toggleCollapse">
            </el-switch>
            <span class="ml-10">{{ scheduleColumnsCollapsed ? __('Развернуть') : __('Свернуть') }} {{ __('листы') }}</span>
        </span>
        <slot name="filter" />
        <a 
            href="#"
            @click.prevent="searchDaySheet">
            <svg-icon name="plus-alt" class="icon-small icon-blue">
                {{ __('Добавить лист записей') }}
            </svg-icon>
        </a>
        <el-dropdown @command="handleCommand" class="action-link">
            <svg-icon name="export-alt" class="icon-small icon-blue">
                {{ __('Дополнительно') }}<i class="el-icon-caret-bottom el-icon--right"></i>
            </svg-icon>
            <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="createCall" v-if="$canCreate('calls')">{{ __('Добавить звонок') }}</el-dropdown-item>
                <el-dropdown-item command="removeAllSheets">{{ __('Закрыть все листы') }}</el-dropdown-item>
                <el-dropdown-item command="clearClipboard" :disabled="!clipboardFilled">{{ __('Очистить буфер обмена') }}</el-dropdown-item>
                <el-dropdown-item command="clearPatient" :disabled="patientMissed">{{ __('Очистить пациента') }}</el-dropdown-item>
            </el-dropdown-menu>
        </el-dropdown>
        <mark-legend />
    </div>
</template>

<script>
import SelectDaySheet from '@/components/appointments/modal/SelectDaySheet.vue';
import FormCreate from '@/components/call-center/calls-appointments/calls/FormCreate.vue';
import MarkLegend from './Legend.vue';
import lts from '@/services/lts';

export default {
    components: {
        MarkLegend,
    },
    props: {
        displayFilter: Boolean,
        patient: Object,
    },
    data() {
        return {
            scheduleColumnsCollapsed: lts.scheduleColumnsCollapsed === true,
        };
    },
    computed: {
        clipboardFilled() {
            let state = this.$store.state;
            return state.clipboard && state.clipboard.type === 'appointment';
        },
        patientMissed() {
            return _.isEmpty(this.patient);
        },
    },
    methods: {
        toggleCollapse() {
            lts.scheduleColumnsCollapsed = this.scheduleColumnsCollapsed;
            this.$eventHub.$emit('columns-collapse', this.scheduleColumnsCollapsed);
        },
        searchDaySheet() {
            this.$modalComponent(SelectDaySheet, {
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, selectionData) => {
                    dialog.close();
                    this.$emit('schedule-add-selected', selectionData);
                },
            }, {
                header: __('Выбор листов записи пациентов'),
                width: '1200px',
                customClass: 'padding-0',
            });
        },
        handleCommand(command) {
            return this[command]();
        },
        removeAllSheets() {
            this.$eventHub.$emit('remove-all-daysheets');
        },
        clearPatient() {
            this.$emit('clear-patient');
        },
        createCall() {
            this.$modalComponent(FormCreate, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('Добавить звонок'),
                width: '1100px',
            });
        },
        clearClipboard() {
            this.$store.commit('clearClipboard');
            this.$eventHub.$emit('clipboard-cleared');
            this.$info(__('Буфер обмена очищен'));
        },
    }
}
</script>