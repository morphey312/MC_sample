<template>
    <div v-loading="loading">
        <div>
            <el-row :gutter="20">
                <el-col :span="16" :offset="4">
                    <form-row 
                        name="clinic_id"
                        :label="__('Выберите клинику')">
                        <el-select v-model="clinic" >
                            <el-option
                                v-for="item in clinics"
                                :key="item.id"
                                :value="item.id"
                                :label="item.value"/>
                        </el-select>
                    </form-row>
                </el-col>
            </el-row>
        </div>
        <div class="form-footer text-right mt-0">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="complete">
                {{ __('Подтвердить') }}
            </el-button>
        </div>
    </div>
</template>
<script>
import ClinicRepository from '@/repositories/clinic';

export default {
    data() {
        return {
            clinic: null,
            clinics: [],
            loading: false,
        }
    },
    mounted() {
        this.getClinics();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        complete() {
            this.$emit('selected', this.clinic);
        },
        getClinics() {
            this.loading = true;
            let clinic = new ClinicRepository();
            return clinic.fetchList({id: this.$store.state.user.clinics}).then(response => {
                this.clinics = response;
                this.loading = false;
            });
        },
    },
}
</script>