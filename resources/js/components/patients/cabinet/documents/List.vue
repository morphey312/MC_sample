<template>
    <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
        <el-tab-pane
            :lazy="true"
            :label="__('Распечатанные')"
            name="printed" >
            <section class="pt-0 shrinkable">
                <printed-document-list
                    :filters="filters"
                    :patient="patient">
                </printed-document-list>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Подписанные')"
            name="signed" >
            <section class="pt-0 shrinkable">
                <signed-document-list
                    :filters="filters"
                    :patient="patient"
                    @header-filter-updated="syncFilters">
                </signed-document-list>
            </section>
        </el-tab-pane>
        <el-tab-pane
            v-if="$canAccess('patient-uploads')"
            :lazy="true"
            :label="__('Предоставленные пациентом')"
            name="provided" >
            <section class="pt-0 shrinkable">
                <provided-document-list :patient="patient" />
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Чеки Checkbox')"
            name="checkboxChecks" >
            <section class="pt-0 shrinkable">
                <checks-list :patient="patient" />
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Акты и счета')"
            name="acts" >
            <section class="pt-0 shrinkable">
                <appointment-document-list :patient="patient" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import PrintedDocumentList from './ListPrinted.vue';
import SignedDocumentList from './ListSigned.vue';
import ProvidedDocumentList from './ListProvided.vue';
import AppointmentDocumentList from './ListAppointment.vue';
import ChecksList from "./ListChecks";

export default {
    components: {
        PrintedDocumentList,
        SignedDocumentList,
        ProvidedDocumentList,
        AppointmentDocumentList,
        ChecksList,
    },
    props: {
        filters: Object,
        patient: Object,
    },
    data() {
        return {
            activeTab: 'printed'
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
    },
};
</script>
