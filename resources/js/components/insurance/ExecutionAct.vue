<template>
    <page
        :title="__('Акты страховых компаний')"
        type="flex"
        v-loading="loading">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <el-tabs v-model="activeTab" class="tab-group-grey insurance-service-act">
            <el-tab-pane
                :lazy="true"
                :label="__('Сформированные акты')"
                name="exported" >
                <insurance-act-table />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Сформировать акт')"
                name="to_export" >
                <service-table />
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('execution-act.clinic-where-employee-works') || $can('execution-act.all-clinics')"
                :lazy="true"
                :label="__('Контроль гарантий')"
                name="control_of_guarantees" >
                <control-of-guarantees />
            </el-tab-pane>
        </el-tabs>
    </page>
</template>
<script>
import InsuranceActTable from './execution-act/Acts.vue';
import ServiceTable from './execution-act/Services.vue';
import ControlOfGuarantees from './control-of-guarantees/Services.vue';

export default {
    components: {
        InsuranceActTable,
        ServiceTable,
        ControlOfGuarantees
    },
    data() {
        return {
            displayFilter: true,
            activeTab: 'exported',
            loading: false,
        }
    },
}
</script>
