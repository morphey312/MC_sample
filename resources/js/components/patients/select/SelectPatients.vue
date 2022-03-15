<template>
    <div v-loading="loading">
        <manage-table 
            ref="table"
            :fields="fields"
            :repository="repository"
            :selectable-rows="true"
            :enable-pagination="false"
            @selection-changed="selectionChanged">
            <div class="buttons" slot="footer-top">
                <el-button
                    @click="add">
                    {{ __('Добавить пациента') }}
                </el-button>
                <el-button
                    :disabled="activeItem === null"
                    @click="remove">
                    {{ __('Удалить') }}
                </el-button>
            </div>
        </manage-table>
    </div>
</template>

<script>
import PatientRepository from '@/repositories/patient';
import ProxyRepository from '@/repositories/proxy-repository';
import SearchPatient from '@/components/patients/search/Search.vue';
import {phoneNumberFormat, listFormat} from '@/services/format';
import TogglePatientFilter from '@/components/patients/search/ToggleFilter.vue';

export default {
    props: {
        value: {
            type: Array,
            default: () => [],
        },
        fields: {
            type: Array,
            default: () => [{
                name: 'full_name',
                title: __('ФИО'),
                width: '40%',
            }, {
                name: 'contact_details',
                title: __('Телефон'),
                width: '30%',
                formatter: (value) => {
                    return listFormat([
                        phoneNumberFormat(value.primary_phone_number), 
                        phoneNumberFormat(value.secondary_phone_number),
                    ]);
                },
            }, {
                name: 'clinic_names',
                title: __('Клиники'),
                width: '30%',
                formatter: (value) => {
                    return value ? value.join(', ') : '';
                },
            }],
        },
    },
    data() {
        return {
            loading: false,
            patients: [],
            activeItem: null,
            repository: new ProxyRepository(() => {
                return Promise.resolve({rows: this.patients});
            }),
        };
    },
    mounted() {
        if (this.value.length !== 0) {
            this.loadPatients(this.value);
        }
    },
    methods: {
        selectionChanged(selection) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
        },
        loadPatients(ids) {
            let repository = new PatientRepository();
            this.loading = true;
            repository.fetch({id: ids}, null, null, 1, 1000).then((result) => {
                result.rows.forEach((row) => {
                    this.patients.push(row);
                });
                this.loading = false;
                this.$refs.table.refresh();
            });
        },
        add() {
            this.$modalComponent(SearchPatient, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    let existing = _.find(this.patients, (item) => patient.id == item.id);
                    if (existing === undefined) {
                        this.patients.push(patient);
                        this.$emit('input', this.patients.map((item) => item.id));
                        dialog.close();
                    } else {
                        this.$warning(__('Этот пациент уже был добавлен'));
                    }
                },
            }, {
                header: __('Фильтр поиска контактных лиц'),
                width: '1270px',
                headerAddon: {
                    component: TogglePatientFilter,
                    eventListeners: {
                        toggleFilter: (dialog, displayFilter) => {
                            dialog.getTopComponent().toggleFilter(displayFilter);
                        },
                    },
                },
            });
        },
        remove() {
            _.remove(this.patients, (patient) => patient.id == this.activeItem.id);
            this.activeItem = null;
            this.$emit('input', this.patients.map((item) => item.id));
        },
    },
    watch: {
        value(val) {
            _.remove(this.patients, (patient) => val.indexOf(patient.id) === -1);
            this.$refs.table.refresh();
        },
    },
}
</script>