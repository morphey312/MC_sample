<template>
    <page
        type="flex"
        :title="__('Статусы записей пациентов')" >
        <el-tabs v-model="activeTab" class="tab-group-beige shrinkable-tabs">
            <el-tab-pane
                v-if="$can('appointment-statuses.access')"
                :lazy="true"
                :label="__('Статусы записей')"
                name="statuses">
                <section class="shrinkable pt-0">
                    <statuses />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('appointment-status-reasons.access')"
                :lazy="true"
                :label="__('Причины изменения статуса')"
                name="reasons">
                <section class="shrinkable pt-0">
                    <reasons />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('appointment-status-delay-reasons.access')"
                :lazy="true"
                :label="__('Причины задержки изменения статуса')"
                name="delay-reasons">
                <section class="shrinkable pt-0">
                    <delay-reasons />
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import Statuses from './appointment-statuses/Statuses.vue';
import Reasons from './appointment-statuses/Reasons.vue';
import DelayReasons from './appointment-statuses/DelayReasons.vue';

export default {
    components: {
        Statuses,
        Reasons,
        DelayReasons,
    },
    data() {
        return {
            activeTab: 'statuses',
        }
    },
}
</script>