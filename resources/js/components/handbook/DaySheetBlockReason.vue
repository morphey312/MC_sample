<template>
    <page
        :title="__('Причины блокировки и разблокировки времени записи')"
        type="flex"
        >
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                :label="__('Причины блокировки времени записи')"
                name="block_reasons" >
                <section class=" shrinkable">
                    <day-sheet-reason />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Причины разблокировки времени записи')"
                name="unblock_reasons" >
                <section class=" shrinkable">
                    <reason-unblock />
                </section>
            </el-tab-pane>

        </el-tabs>
    </page>
</template>

<script>
import DaySheetReason from './DaySheetBlockReasons.vue';
import ReasonUnblock from './DaySheetUnBlockReasons.vue';


export default {
    components: {
        DaySheetReason,
        ReasonUnblock
    },
    data() {
        let defaultTab = 'block_reasons';


        return {
            activeTab: defaultTab,
        }
    },
};
</script>
