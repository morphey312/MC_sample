<template>
    <model-form :model="model">
        <slot name="header"></slot>
        <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
            <el-tab-pane
                v-if="!$can('services.update-pc-only')"
                :lazy="true"
                :label="__('Общие настройки')"
                name="general-settings" >
                <section>
                    <general-settings
                        :model="model"
                        :limit-clinics="limitClinics" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Настройки для онлайн ЛК')"
                name="pc-settings" >
                <section>
                    <personal-cabinet-settings
                        :model="model" />
                </section>
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import GeneralSettings from "./form-tabs/GeneralSettings";
import PersonalCabinetSettings from "./form-tabs/PersonalCabinetSettings";

export default {
    props: {
        model: Object,
        limitClinics: {
            type: Boolean,
            default: false,
        },
    },
    components: {
        GeneralSettings,
        PersonalCabinetSettings,
    },
    data() {
        let activeTab = 'general-settings';
        if (this.$can('services.update-pc-only')) {
            activeTab = 'pc-settings';
        }

        return {
            activeTab: activeTab,
        }
    }
}
</script>
