<template>
    <div class="day-sheet-item">
        <template v-if="hasTimeSheets">
            <div v-for="(timeSheet, index) in timeSheets" :key="index">
                <el-tooltip
                    placement="right"
                    effect="light"
                    :open-delay="500"
                    popper-class="light-popover-content"
                    :content="`${timeRange(timeSheet)} ${specializationList(timeSheet)}`">
                    <div class="day-sheet-item-list">
                        {{ timeRange(timeSheet) }}
                        {{ specializationList(timeSheet) }}
                    </div>
                </el-tooltip>
            </div>
        </template>
    </div>
</template>

<script>
export default {
    props: {
        item: Object,
    },
    data() {
        return {
            timeSheets: [],
        }
    },
    computed: {
        hasTimeSheets() {
            return this.timeSheets.length !== 0;
        },
    },
    mounted() {
        this.setTimeSheets();
    },
    methods: {
        setTimeSheets() {
            if (this.item.event.time_sheets) {
                this.item.event.title = ' ';
                this.timeSheets = this.item.event.time_sheets;
            }
        },
        timeRange(timeSheet) {
            let timeFrom = timeSheet.time_from.substring(0, 5);
            let timeTo = timeSheet.time_to.substring(0, 5);
            return `${timeFrom} - ${timeTo}`;
        },
        specializationList(timeSheet) {
            return timeSheet.event_data.map(item => {
                return item.specialization_name + (_.isFilled(item.workspace_name) ? '. ' + item.workspace_name : '');
            }).join('/');
        },
    },
}
</script>