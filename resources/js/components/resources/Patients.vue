<template>
    <page
        :title="__('Пациенты')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                :label="__('Поиск')"
                name="patients" >
                <patients :display-filter-prop="displayFilter"/>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Поиск eHealth')"
                name="ehealth-patients" >
                <ehealth-search-patients/>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import Patients from '@/components/patients/Patients.vue';
import EhealthSearchPatients from '@/components/ehealth/SearchPatient.vue';

export default {
    components: {
        Patients,
        EhealthSearchPatients,
    },
    data() {
        return {
            activeTab: 'patients',
            displayFilter: true,
        }
    },
}
</script>

