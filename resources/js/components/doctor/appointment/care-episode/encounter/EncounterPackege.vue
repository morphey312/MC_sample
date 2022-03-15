<template>
    <div>
        <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :label="__('Запись')"
                name="encounter">
                <section>
                    <encounter
                        :encounter="encounter"/>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Состояние')"
                name="conditions">
                <section v-if="tabConditions">
                    <conditions
                        v-if="encounter.id"
                        :encounter="encounter"
                        :outpatientRecord="outpatientRecord"/>
                    <save-encounter v-else @createEncounter="createEncounter"/>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Диагностика')"
                name="diagnostics">
                <section v-if="tabDiagnostics">
                    <diagnostics
                        v-if="encounter.id"
                        :encounter="encounter"
                        :appointment="appointment"/>
                    <save-encounter v-else @createEncounter="createEncounter"/>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Процедуры')"
                name="procedures">
                <section v-if="tabProcedures">
                    <procedures
                        v-if="encounter.id"
                        :encounter="encounter"
                        :appointment="appointment"/>
                    <save-encounter v-else @createEncounter="createEncounter"/>
                </section>
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script>
import FormTab from './mixins/form-tabs';
import Encounter from './Encounter';
import Conditions from './condition/Conditions';
import Diagnostics from './diagnostic-report/Diagnostics';
import Procedures from './procedure/Procedures';
import SaveEncounter from './blocks/SaveEncounter';

export default {
    mixins: [
        FormTab
    ],
    components: {
        Diagnostics,
        Encounter,
        Conditions,
        Procedures,
        SaveEncounter,
    },
    props: {
        encounter: Object,
        appointment: Object,
        outpatientRecord: Object
    },
    watch: {
        ['encounter.id'](val) {
            this.$clearErrors()
        },
    },
    methods: {
        createEncounter() {
            this.activeTab = 'encounter';
            this.$emit('saveEncounter');
        },
    }
}
</script>
