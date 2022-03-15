<template>
    <section>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-row
                    name="active-clinic">
                    <form-select
                        :entity="clinic"
                        :options="clinics"
                        property="id"
                        :filterable="true"
                        :label="__('Клиника')"
                    />
                </form-row>
            </el-col>
        </el-row>
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="next">
                {{ __('Продолжить') }}
            </el-button>
        </div>
    </section>
</template>
<script>
import ClinicRepository from '@/repositories/clinic';
import PatientQuestionnaire from '@/components/patients/questionnaire/Blank';

export default {
    props: {
        patient: Object,
    },
    data() {
        return {
            clinic: {
                id: null
            },
            clinics: new ClinicRepository({
                filters: {
                    id: this.patient.clinics
                }
            })
        };
    },
    beforeMount() {
        if (this.patient.clinics.length === 1) {
            this.clinic.id = this.patient.clinics[0];
            this.cancel();
            this.next();
        }
    },
    methods: {
        cancel() {
            this.$emit('close');
        },
        next() {
            this.$modalComponent(PatientQuestionnaire, {
                patientId: this.patient.id,
                clinicId: this.clinic.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                    this.cancel();
                },
                saved: (dialog) => {
                    this.$emit('saved');
                    dialog.close();
                    this.cancel();
                }
            }, {
                header: __('Анкета пациента'),
                width: '1000px',
                customClass: 'no-footer',
            });
        },
    }
}
</script>
