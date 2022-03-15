<template>
    <model-form :model="encounter">
        <el-row :gutter="20">
            <el-col :span="12">
                <form-select
                    :entity="encounter"
                    property="type"
                    :label="__('Тип взаимодействия')"
                    options="ehealth_encounter_types" />
            </el-col>
            <el-col :span="12">
                <form-select
                    :entity="encounter"
                    :repository="reasons"
                    :clearable="true"
                    :multiple="true"
                    property="reasons"
                    :label="__('Причины обращения')" />
            </el-col>
        </el-row>
        <el-row :gutter="20" style="display: flex; align-items: center">
            <el-col :span="8">
                <form-switch
                    :entity="encounter"
                    options="encounter_type_referral"
                    property="paper_referral"
                    :label="__('Тип направления')"/>
            </el-col>
            <el-col :span="8">
                <form-checkbox
                    style="margin: 0; text-align: center"
                    :entity="encounter"
                    property="hospitalization"
                    :label="__('Госпитализация')"
                />
            </el-col>
            <el-col :span="8">
                <form-select
                    :entity="encounter"
                    property="priority"
                    :label="__('Приоритет')"
                    options="ehealth_encounter_priority" />
            </el-col>
        </el-row>
        <div v-if="isIncoming" style="margin-top: 20px; margin-bottom: 20px">
            <el-row :gutter="20">
                <el-col :span="24">
                    <form-input
                        :entity="encounter"
                        property="incoming_referral"
                        :label="__('Индетификатор направления')"/>
                </el-col>
            </el-row>
        </div>
        <div v-if="isPaper" style="margin-top: 20px; margin-bottom: 20px">
            <paper-referral-block :model="encounter"/>
        </div>
        <div v-if="encounter.hospitalization" :style="isIncoming || isPaper ? '' : 'margin-top: 20px'" style="margin-bottom: 20px">
            <el-row :gutter="20">
                <el-col :span="12">
                    <form-select
                        :entity="encounter"
                        property="hospitalization_admit_source"
                        :label="__('Причина обращения')"
                        options="ehealth_encounter_admit_source" />
                </el-col>
                <el-col :span="12">
                    <form-select
                        :entity="encounter"
                        property="hospitalization_re_admission"
                        :label="__('Признак вторичного обращения')"
                        options="ehealth_encounter_re_admission" />
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="24">
                    <div style="display: flex; align-items: center">
                        <form-input
                            :entity="encounter"
                            property="hospitalization_destination"
                            :label="__('Заведение, в которок перевели пациента')"
                            :disabled="true"/>
                        <el-button
                            style="margin-left: 20px"
                            @click="create">
                            {{ __('Найти') }}
                        </el-button>
                        <el-button
                            style="margin-left: 20px"
                            v-if="encounter.hospitalization_destination"
                            @click="clear">
                            {{ __('Очистить') }}
                        </el-button>
                    </div>
                </el-col>
            </el-row>
            <el-row :gutter="20">
                <el-col :span="12">
                    <form-select
                        :entity="encounter"
                        property="hospitalization_discharge_disposition"
                        :label="__('Результат Лечения')"
                        options="ehealth_encounter_discharge_disposition" />
                </el-col>
                <el-col :span="12">
                    <form-input
                        :entity="encounter"
                        property="hospitalization_pre_admission_identifier"
                        :label="__('Номер вызова скорой помощи')"/>
                </el-col>
            </el-row>
        </div>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-text
                    :entity="encounter"
                    property="prescriptions"
                    :label="__('Назначение доктора')"
                />
            </el-col>
        </el-row>
    </model-form>
</template>

<script>
import CONSTANTS from '@/constants';
import ManageMixin from '@/mixins/manage';
import EhealthReasonRepository from '@/repositories/ehealth/encounter/hand-book/reason';
import PaperReferralBlock from './blocks/PaperReferral.vue';
import SearchMsp from '@/components/msp/msp/form-tabs/contracts/contractors/SearchMsp';
import FormEdit from '@/components/msp/msp/form-tabs/contracts/contractors/FormEdit';
import FormCreate from '@/components/msp/msp/form-tabs/contracts/contractors/FormCreate';

export default {
    components: {
        PaperReferralBlock
    },
    props: {
        encounter: Object
    },
    mixins: [
        ManageMixin,
    ],
    data() {
        return {
            reasons: new EhealthReasonRepository(),
        };
    },
    methods: {
        clear() {
            this.encounter.hospitalization_destination = null;
        },
        getModalOptions() {
            return {
                createForm: SearchMsp,
                editForm: FormEdit,
                editProps: () => ({
                    clinics: this.clinics,
                }),
                createHeader: __('Добавить заведение, в которок перевели пациента'),
                editHeader: __('Изменить заведение, в которок перевели пациента'),
                width: '600px',
                events: {
                    next: (dialog, le) => {
                        dialog.pushComponent(FormCreate, {
                            legalEntity: le,
                            clinics: this.clinics,
                        }, {
                            cancel: (dialog) => {
                                dialog.close();
                            },
                            created: (dialog, model) => {
                                dialog.close();
                                this.$info(__('информация была успешно добавлена'));
                                console.log(model)
                                this.encounter.hospitalization_destination = model.edrpou;
                            },
                        })
                    }
                }
            };
        },
    },
    computed: {
        isPaper() {
            return this.encounter.paper_referral === CONSTANTS.ENCTOUNTER.TYPE_REFERRAL.PAPER
        },
        isIncoming() {
            return this.encounter.paper_referral === CONSTANTS.ENCTOUNTER.TYPE_REFERRAL.INCOMING
        }
    }
}
</script>
