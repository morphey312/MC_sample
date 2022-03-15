<template>
    <section>
        <el-row :gutter="62">
            <el-col :span="12">
                <form-row
                    name="active-clinic"
                    :label="__('Клиника')">
                    <el-select
                        :filterable="true"
                        v-model="activeClinic">
                        <el-option
                            v-for="clinic in model.clinics"
                            :key="clinic.clinic_id"
                            :value="clinic.clinic_id"
                            :label="clinic.name"
                        />
                    </el-select>
                </form-row>
            </el-col>
        </el-row>
        <form-row name="specializations">
            <transfer-table
                ref="transfer"
                key="specializations"
                :items="specializations"
                v-model="clinicData"
                :left-title="__('Специализация')"
                left-width="310px"
                :right-title="__('Специализация')"
                right-width="310px"
                :emptySelectionMessage="placeholder">
            </transfer-table>
        </form-row>
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="updateModel">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </section>
</template>
<script>
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';

export default {
    props: {
        model: Object,
    },
    data() {
        return {
            clinics: [],
            specializations: [],
            clinicData: [],
            activeClinic: null,
            placeholder: this.getPlaceholder(),
        }
    },
    mounted() {
        this.setDefaultClinic();
        this.getSpecializations();
    },
    methods: {
        getSpecializations() {
            let filter = _.onlyFilled({
                status: 1,
                active_clinic: this.activeClinic
            });

            if(!this.model.isNew()){
                filter.skipId = this.model.id;
            }

            let specialization = new SpecializationRepository();
            specialization.fetchList(filter).then((response) => {
                this.specializations = response;
            });
        },
        setDefaultClinic() {
            this.activeClinic = this.model.clinics[0] ? this.model.clinics[0].clinic_id : null;
        },
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Выберите и добавьте специализацию слева'));
        },
        cancel() {
            this.$emit('cancel');
        },
        updateModel() {
            this.$emit('specialization-updated');
        },
    },
    watch: {
        activeClinic(val) {
            if (val && this.model.adjacent_specializations && this.model.adjacent_specializations[val]){
                this.clinicData = [...this.model.adjacent_specializations[val]];
            } else{
                this.clinicData = [];
            }
            this.getSpecializations();
            this.$nextTick(() => {
                this.$refs.transfer.initItems();
            });
        },
        clinicData(val) {
            if(val) {
                if (!this.model.adjacent_specializations) {
                    this.model.adjacent_specializations = {};
                }
                this.model.adjacent_specializations[this.activeClinic] = [...val];
            }
        },
    }
}
</script>
