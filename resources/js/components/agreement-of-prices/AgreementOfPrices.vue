<template>
    <page
        :title="__('Согласование прайсов')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                :label="__('Прайсы для согласования')"
                name="acts">
                <acts />
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('price-agreement-acts.create-service-clinic') || $can('price-agreement-acts.create-service-clinic')"
                :lazy="true"
                :label="__('Услуги')"
                name="services">
                <service-list />
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('price-agreement-acts.create-analysis-clinic') || $can('price-agreement-acts.create-analysis-clinic')"
                :lazy="true"
                :label="__('Анализы')"
                name="analyzes">
                <analysis-list />
            </el-tab-pane>
             <el-tab-pane
                :label="__('Измененные прайсы')"
                name="prices">
                <prices-export />
            </el-tab-pane>
        </el-tabs>
         <template slot="header-addon">
                <legent ref="buttons"/>
            </template>
    </page>
</template>

<script>
import AnalysisList from './analysis/AnalysisList.vue';
import PricesExport from './PricesExport.vue';
import Legent from './Legent.vue';
import ServiceList from './services/ServiceList.vue';
import Acts from './Acts.vue';

export default {
    components: {
        PricesExport,
        AnalysisList,
        ServiceList,
        Acts,
        Legent,
    },
    data() {
        return {
            activeTab: 'acts',
        }
    },
}
</script>