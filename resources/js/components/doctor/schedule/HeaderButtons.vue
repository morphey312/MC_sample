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
        <mark-legend />
    </div>
</template>

<script>
import SelectDaySheet from '@/components/appointments/modal/SelectDaySheet.vue';
import FormCreate from '@/components/call-center/calls-appointments/calls/FormCreate.vue';
import MarkLegend from '@/components/appointments/schedule/parts/Legend.vue';
import lts from '@/services/lts';

export default {
    components: {
        MarkLegend,
    },
    props: {
        displayFilter: Boolean,
    },
    data() {
        return {
            scheduleColumnsCollapsed: lts.scheduleColumnsCollapsed === true,
        };
    },
    methods: {
        toggleCollapse() {
            lts.scheduleColumnsCollapsed = this.scheduleColumnsCollapsed;
            this.$eventHub.$emit('columns-collapse', this.scheduleColumnsCollapsed);
        },
        searchDaySheet() {
            this.$modalComponent(SelectDaySheet, {
                doctor: this.$store.state.user.employee_id,
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
    }
}
</script>