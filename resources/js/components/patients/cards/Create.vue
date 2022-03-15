<template>
    <form>
        <form-select
            :entity="model"
            :options="specializations"
            property="specialization_id"
            :filterable="true"
            :label="__('Специализация')"
        />
        <div class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </form>
</template>

<script>
import CardSpecialization from '@/models/patient/card/card-specialization';
import SpecializationRepository from '@/repositories/specialization';

export default {
    props: {
        patient: Object,
    },
    data() {
        let clinicId = this.patient.cards.length === 0 ? null : this.patient.cards[0].clinic_id;
        return {
            clinicId,
            specializations: [],
            model: new CardSpecialization({patient_id: this.patient.id, clinic_id: clinicId}),
        };
    },
    watch: {
        ['model.clinic_id']() {
            this.loadSpecializations();
        },
    },
    mounted() {
        this.model.clinic_id = this.clinicId || this.patient.clinics[0];
        this.loadSpecializations();
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.validate().then((errors) => {
                if(_.isEmpty(errors)) {
                    this.model.set('specialization', this.getCardSpecializationAttribute());
                    this.$info(__('Карта была успешно добавлена'));
                    this.$emit('created', this.model);
                    return;
                }

                return this.$displayErrors({errors});
            });
        },
        getCardSpecializationAttribute() {
            let attributeData = { name: '', short_name: '' };

            let specialization = this.specializations.find(specialization => specialization.id == this.model.specialization_id);

            if(specialization) {
                attributeData.name = specialization.value;
                attributeData.short_name = specialization.short_name;
            }

            return attributeData;
        },
        getCardSpecializations() {
            if(this.patient.cards.length === 0) {
                return [];
            }

            let clinic = this.patient.cards[0];
            let list = [];

            clinic.specializations.forEach(item => {
                list.push(item.specialization_id);
            });

            return list;
        },
        loadSpecializations() {
            this.specializations = [];
            let repo = new SpecializationRepository();

            repo.fetchList({
                clinic: this.patient.appointmentClinic ? [this.patient.appointmentClinic] : this.patient.clinics,
                skipId: this.getCardSpecializations(),
            }).then((list) => {
                this.specializations = list;
            });
        },
    },
};
</script>
