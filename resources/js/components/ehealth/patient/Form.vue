<template>
    <model-form :model="model">
        <slot name="header"></slot>
        <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :lazy="true"
                :label="__('Общие данные')"
                name="personal-info" >
                <error-catcher :catch="authenticationsErrors" />
                <personal-info
                    :model="model"/>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Адрес')"
                name="cards" >
                <patient-address
                    :model="model"/>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Документы')"
                name="documents" >
                <error-catcher :catch="documentsErrors" />
                <documents
                    :model="model"/>
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Доверенная личность')"
                name="trusted-person" >
                <trusted-person
                    :model="model"/>
            </el-tab-pane>
            <el-tab-pane
                v-if="(model.birth_date && model.age < 14) || model.incompetent"
                :lazy="true"
                :label="__('Законный представитель')"
                name="confidant-persons" >
                <confidant-person
                    :model="model.confidant_person"/>
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import PersonalInfo from './ehealth-form-tabs/PersonalInfo.vue';
import PatientAddress from './ehealth-form-tabs/PatientAddress.vue';
import Documents from './ehealth-form-tabs/Documents.vue';
import TrustedPerson from './ehealth-form-tabs/TrustedPerson.vue';
import ConfidantPerson from './ehealth-form-tabs/ConfidantPerson.vue';
import ConfidantPersonModel from '@/models/ehealth/patient/confidant-person'

export default {
    props: {
        model: {
            type: Object
        },
    },
    components: {
        ConfidantPerson,
        PersonalInfo,
        PatientAddress,
        Documents,
        TrustedPerson,
    },
    watch: {
        ['model.birth_date']() {
            this.checkNeededInConfidantPerson()
        },
        ['model.incompetent']() {
            this.checkNeededInConfidantPerson()
        }
    },
    data() {
        return {
            activeTab: 'personal-info',
            documentsErrors: [
                {key: 'documents', label: __('Документы')},
            ],
            authenticationsErrors: [
                {key: 'authentication', label: __('Аунтификация')},
            ],
        };
    },
    methods: {
        checkNeededInConfidantPerson() {
            if (this.model.need_confidant_person) {
                this.model.confidant_person = new ConfidantPersonModel();
            } else {
                this.model.confidant_person = {};
            }
        }
    }
};
</script>
