<template>
    <model-form :model="model">
        <el-tabs 
            v-model="activeTab" 
            class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :label="__('Данные о юр. лице')"
                name="info">
                <form-info :model="model" />
            </el-tab-pane>
            <el-tab-pane
                :label="__('Аккредитация/Лицензия')"
                name="license">
                <form-license :model="model" />
            </el-tab-pane>
            <el-tab-pane
                :label="__('Архивы')"
                name="archives">
                <form-archives :msp="model" />
            </el-tab-pane>
            <el-tab-pane
                :label="__('Руководитель')"
                name="owner">
                <form-owner :model="model" />
            </el-tab-pane>
            <el-tab-pane
                v-if="model.get('edr_data')"
                :label="__('Статус/Данные ЕГР')"
                name="edr">
                <section>
                    <edr-data :model="model" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="canHaveContracts && $can('msp-contracts.access')"
                :lazy="true"
                :label="__('Договоры')"
                name="contracts">
                <list-contracts 
                    :msp="model"
                    :modal-component="modalComponent" />
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import FormInfo from './form-tabs/Info.vue';
import FormLicense from './form-tabs/License.vue';
import FormArchives from './form-tabs/Archives.vue';
import FormOwner from './form-tabs/Owner.vue';
import ListContracts from './form-tabs/Contracts.vue';
import EdrData from './form-tabs/Edr.vue';
import CONSTANT from '@/constants';

export default {
    components: {
        FormInfo,
        FormLicense,
        FormArchives,
        FormOwner,
        ListContracts,
        EdrData,
    },
    props: {
        model: {
            type: Object
        },
        modalComponent: {
            type: Object
        },
    },
    computed: {
        canHaveContracts() {
            return this.model.type === CONSTANT.EHEALTH.MSP_TYPE.PRIMARY_CARE
                && _.isFilled(this.model.owner.ehealth_id);
        }
    },
    data() {
        return {
            activeTab: 'info',
        };
    },
}
</script>