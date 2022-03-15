<template>
    <div>
        <form-row name="clinics">
            <transfer-table
                :items="clinics"
                :fields="isAnalysis ? [...clinicFields, ...extraFields] : clinicFields"
                v-model="newClinics"
                value-key="id"
                :left-title="__('Клиника')"
                left-width="190px"
                :checked-callback="checkedRowCallback"
                :right-title="__('Клиника')"
                right-width="190px">
                    <template slot="code" slot-scope="props">
                        <form-input
                            :entity="props.rowData.data"
                            property="code"
                            control-size="mini"
                            css-class="table-row" />
                    </template>
                    <template slot="duration_days" slot-scope="props">
                        <form-input-number
                            :entity="props.rowData.data"
                            property="duration_days"
                            :min="0"
                            control-size="mini"
                            css-class="table-row" />
                    </template>
          </transfer-table>
        </form-row>
        <div
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="addClinics">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import ClinicRepository from "@/repositories/clinic";
import CONSTANTS from "@/constants";

export default {
    props: {
        clinicsWithCodes: {
            type: Array,
            required: true
        },
        serviceType: {
            type: String,
            required: true
        },
    },
    computed: {
        isAnalysis() {
            return this.serviceType === CONSTANTS.PRICE.SERVICE_TYPE.ANALYSIS;
        },
    },
    mounted() {
        this.getClinics();
    },
    data() {
        return {
            clinics: [],
            newClinics: this.clinicsWithCodes,
            clinicFields: [
                {
                    name: 'code',
                    title: __('Код'),
                    width: '80px',
                },
            ],
            extraFields: [
                {
                    name: 'duration_days',
                    title: __('Кол-во дней'),
                    width: '80px',
                },
            ]
        }
    },
    methods: {
        checkedRowCallback(table) {
            if (table) {
                let selected = table.selectedTo;
                this.clinicsWithCodes.forEach(clinic => {
                    let index = selected.findIndex(id => clinic.id == id);
                    if (index != -1) {
                        selected.splice(index, 1);
                    }
                });
            }
        },
        getClinicsFilters() {
            if (!this.$can('price-agreement-acts.create-' + this.serviceType)) {
                return _.onlyFilled({
                    id: _.uniq([...this.clinicsWithCodes.map(clinic => clinic.id), ...this.$store.state.user.clinics])
                });
            }
        },
        getClinics() {
            let clinic = new ClinicRepository({
                filters: this.getClinicsFilters()
            });
            clinic.fetchList().then((response) => {
                this.clinics = response;
                this.loading = false;
            });
        },
        cancel() {
            this.$emit('cancel');
        },
        addClinics() {
            let clinics = []
            this.newClinics.forEach(clinic => {
                let index = this.clinicsWithCodes.findIndex(existClinic => clinic.id === existClinic.id);
                if (index === -1) {
                    clinics.push(clinic);
                }
            });
            this.$emit('addClinics', [this.clinicsWithCodes, clinics]);
        }
    }
}
</script>
