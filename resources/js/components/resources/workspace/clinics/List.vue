<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded">
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import Workspace from '@/models/workspace';
import WorkspaceClinic from '@/models/workspace/clinic';
import ClinicRepository from '@/repositories/clinic';

export default {
    props: {
        workspace: Object,
    },
    data() {
        return {
            model: new Workspace({ id: this.workspace.id }),
            repository: new ProxyRepository(() => {
                return this.getClinics();
            }),
            fields: [
                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                },
                {
                    name: 'specialization_names',
                    title: __('Специализация'),
                    formatter: (value) => {
                        return this.$formatter.listFormat(value);
                    }
                },
                {
                    name: 'appointment_duration',
                    title: __('Длительность приема'),
                },
            ],
            clinic_list: new ClinicRepository(),
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        getClinics() {
            return this.model.fetch(['clinics']).then(() => {
                let clinic = new WorkspaceClinic({
                    limitClinics: this.$isAccessLimited('workspaces')
                });
                return {
                    rows: this.model.workspace_clinics.map(row => clinic.castToInstance(WorkspaceClinic, row)),
                };
            });
        },
    },
}
</script>
