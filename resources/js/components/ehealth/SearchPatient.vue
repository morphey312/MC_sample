<template>
    <div class="sections-wrapper shrinkable-tabs">
        <section class="grey pb-0 pt-10">
            <search-filter
                ref="patientEhealthFilter"
                :model="filter"
                :show-submit-button="true"
                :show-clear-button="true"
                :auto-search="false"
                @changed="changed"
                @cleared="cleared" >
                <el-row :gutter="20">
                    <el-col :span="8">
                        <form-input
                            :entity="filter"
                            :required="true"
                            property="last_name"
                            :label="__('Фамилия пациента')" />
                        <form-date
                            :entity="filter"
                            :required="true"
                            property="birth_date"
                            :label="__('Дата рождения')" />
                    </el-col>
                    <el-col :span="8">
                        <form-input
                            :entity="filter"
                            :required="true"
                            property="first_name"
                            :label="__('Имя пациента')" />
                        <form-input
                            :entity="filter"
                            :clearable="true"
                            property="phone_number"
                            :label="__('Номер телефона')" />
                    </el-col>
                    <el-col :span="8">
                        <form-input
                            :entity="filter"
                            :clearable="true"
                            property="second_name"
                            :label="__('Отчество пациента')" />
                        <form-row
                            name="documents">
                            <div class="form-input-group">
                                <form-input
                                    :entity="filter"
                                    :clearable="true"
                                    property="tax_id"
                                    :label="__('РНУКПН')" />
                                <form-input
                                    :entity="filter"
                                    :clearable="true"
                                    property="birth_certificate"
                                    :label="__('№ свид. о рождении')" />
                            </div>
                        </form-row>
                    </el-col>
                </el-row>
            </search-filter>
        </section>
        <section
            v-if="loadList"
            class="grey-cap">
            <template>
                <manage-table
                    ref="table"
                    :fields="fields"
                    :filters="filter"
                    :repository="repository"
                    :enable-pagination="false">
                    <template
                        slot="patient"
                        slot-scope="props">
                        <a href="#" @click.prevent="selectPatient(props.rowData)">
                            {{ props.rowData.last_name }}
                            {{ props.rowData.first_name }}
                            {{ props.rowData.second_name }}
                        </a>
                    </template>
                    <template slot="footer-top">
                        <slot name="buttons" />
                    </template>
                </manage-table>
            </template>
        </section>
        <section
            v-else
            class="grey-cap shrinkable" style="padding-top: 80px">
            <wait-search-placeholder
                :create-text="__('создайте пациента')"
                :can-create="$canCreate('patients')"
                @create="createEhealthPatientAndSync" />
        </section>
    </div>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import ehealth from '@/services/ehealth';
import PatientCreateMixin from "@/components/patients/mixins/patient-create";

export default {
    mixins: [
        PatientCreateMixin,
    ],
    props: {
        initialFilter: Object,
        patient: Object,
    },
    computed: {
        canSearch() {
            return _.isFilled(this.filter.last_name)
                && _.isFilled(this.filter.first_name)
                && _.isFilled(this.filter.birth_date);
        }
    },
    data() {
        return {
            loadList: false,
            filter: {
                ...this.initialFilter,
            },
            repository: new ProxyRepository(({filters}) => {
                return ehealth.getPatients(this.normalizeFilters(filters)).then((results) => ({
                    rows: results,
                }));
            }),
            fields: [{
                name: 'patient',
                title: __('Пациент'),
                width: '25%',
            }, {
                name: 'birth_date',
                title: __('Дата рождения'),
                width: '15%',
                formatter: (value) => {
                    return this.$formatter.dateFormat(value);
                },
            }, {
                name: 'gender',
                title: __('Пол'),
                width: '15%',
                formatter: (value) => {
                    return this.$handbook.getOption('gender', value);
                },
            }, {
                name: 'birth_country',
                title: __('Страна'),
                width: '15%',
            }, {
                name: 'birth_settlement',
                title: __('Город'),
                width: '15%',
            }],
        };
    },
    methods: {
        createEhealthPatientAndSync() {
            this.createEhealthPatient(this.patient,{}, (patient) => {
                this.$nextTick(() => {
                    this.$emit('created', patient);
                });
            });
        },
        changed(filters) {
            this.filter = _.onlyFilled(filters);
            this.loadList = this.canSearch;
        },
        cleared() {
            this.filter = {};
            this.loadList = this.canSearch;
        },
        normalizeFilters(filters) {
            let normalized = {...filters};
            if (_.isFilled(normalized.phone_number)) {
                normalized.phone_number = `+38${normalized.phone_number.substr(0, 10)}`;
            }
            return normalized;
        },
        selectPatient(data) {
            this.$emit('selected', data);
        },
    }
}
</script>
