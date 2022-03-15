<template>
    <section>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-row
                    name="protocol_name"
                    :label="__('Имя документа')">
                    <el-input
                        v-model="model.name"
                        :placeholder="__('Введите имя')"
                    />
                </form-row>
            </el-col>
            <el-col :span="24">
                <form-row
                    name="active-clinic"
                    :label="__('Специализация')">
                    <el-select v-model="model.card_specialization_id">
                        <el-option
                            v-for="card in cards"
                            :key="card.id"
                            :value="card.id"
                            :label="card.specialization.name"
                        />
                    </el-select>
                </form-row>
                <form-select
                    :entity="model"
                    :filterable="false"
                    :min-query-len="0"
                    :repository="doctors"
                    property="doctor_id"
                    :label="__('Врач')"
                />
                <form-row
                    name="protocol_file"
                    :label="__('Выберите файл')">
                    <form-upload
                        ref="attachments"
                        :entity="model"
                        :limit="1"
                        accept="image/jpeg,image/png/,application/pdf"
                        property="attachments"
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
                @click="create">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </section>
</template>
<script>
import Document from '@/models/patient/card/document';
import PrintedDocument from "@/models/patient/card/printed-document";
import EmployeeRepository from '@/repositories/employee';
import CONSTANTS  from '@/constants';

export default {
    props: {
        patient: Object,
    },
    data() {
        return {
            cards: [],
            model: null,
            doctors: new EmployeeRepository({
                filters: {
                    positionType: CONSTANTS.EMPLOYEE.POSITIONS.DOCTOR,
                    has_patient_appointment: this.patient.id,
                },
            })
        };
    },
    beforeMount() {
        this.init();
    },
    methods: {
        init() {
            this.model = new Document({
                patient_id: this.patient.id,
            });

            this.cards = this.patient.getCardsWithSpecializations();
        },
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.model.save().then(() => {
                this.$info(__('Документ успешно сохранен'));
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    }
}
</script>
