<template>
    <model-form :model="model">
        <el-row :gutter="20">
            <el-col :span="-15">
                <form-select
                    :entity="model"
                    options="blood_group"
                    property="blood_group"
                    :label="__('Группа крови')" />
            </el-col>
            <el-col :span="-15">
                <form-select
                    :entity="model"
                    options="rhesus_factor"
                    property="rhesus_factor"
                    :label="__('Резус фактор')" />
            </el-col>
            <el-col :span="-15">
                <form-select
                    :entity="model"
                    options="diabetes"
                    property="diabetes"
                    :label="__('Сахарный диабет')" />
            </el-col>
            <el-col :span="-15">
                <form-input
                    :entity="model"
                    property="transfusion_comment"
                    :label="__('Переливание крови')">
                    <form-yesno-addon
                        slot="label-addon"
                        v-model="model.transfusion" />
                </form-input>
            </el-col>
            <el-col :span="-15">
                <form-date
                    :entity="model"
                    :disabled="!model.onco_observation_vil"
                    :options="pickerOptions"
                    :label="__('Cкрининг ВИЛ')"
                    property="onco_observation_vil_date"
                >
                <form-yesno-addon
                        slot="label-addon"
                        v-model="model.onco_observation_vil" />
                </form-date>
            </el-col>
        </el-row>

        <el-row :gutter="20">
            <el-col :span="-15">
                <form-date
                    :disabled="!model.onco_observation_gyn"
                    :entity="model"
                    :label="__('Онкоскрининг ГИН')"
                    :options="pickerOptions"
                    property="onco_observation_gyn_date"
                >
                    <form-yesno-addon
                        slot="label-addon"
                        v-model="model.onco_observation_gyn" />
                </form-date>
            </el-col>
            <el-col :span="-15">


                <form-date
                    :entity="model"
                    :disabled="!model.onco_observation_pro"
                    :options="pickerOptions"
                    :label="__('Онкоскрининг ПРО')"
                    property="onco_observation_pro_date"
                >
                    <form-yesno-addon
                        slot="label-addon"
                        v-model="model.onco_observation_pro" />
                </form-date>
            </el-col>
            <el-col :span="-15">

                <form-date
                    :entity="model"
                    :label="__('Онкоскрининг УРО')"
                    :disabled="!model.onco_observation_uro"
                    :options="pickerOptions"
                    property="onco_observation_uro_date"
                >
                    <form-yesno-addon
                        slot="label-addon"
                        v-model="model.onco_observation_uro" />
                </form-date>
            </el-col>
            <el-col :span="-15">


                <form-date
                    :entity="model"
                    :disabled="!model.onco_observation_ren"
                    :options="pickerOptions"
                    :label="__('Скрининг ОГП')"
                    property="onco_observation_ren_date"
                >
                <form-yesno-addon
                        slot="label-addon"
                        v-model="model.onco_observation_ren" />
                </form-date>
            </el-col>
            <el-col :span="-15">
                <form-date
                    :entity="model"
                    :disabled="!model.onco_observation_vaserman"
                    :options="pickerOptions"
                    :label="__('Реакция Вассермана')"
                    property="onco_observation_vaserman_date"
                >
                <form-yesno-addon
                        slot="label-addon"
                        v-model="model.onco_observation_vaserman" />
                </form-date>
            </el-col>


        </el-row>
        <el-row :gutter="20">
            <el-col :span="-15">
                <form-text
                    :entity="model"
                    property="drug_intolerance"
                    :rows="3"
                    :label="__('Непереносимость лекарств')" />
            </el-col>
            <el-col :span="-15">
                <form-text
                    :entity="model"
                    property="infections"
                    :rows="3"
                    :label="__('Инфекционные заболевания')" />
            </el-col>
            <el-col :span="-15">
                <form-text
                    :entity="model"
                    property="surgical_interventions"
                    :rows="3"
                    :label="__('Хирургические вмешательства')" />
            </el-col>
            <el-col :span="-15">
                <form-text
                    :entity="model"
                    property="allergic_history"
                    :rows="3"
                    :label="__('Аллергологический анамнез')" />
            </el-col>
            <el-col :span="-15">
                <form-text
                    :entity="model"
                    property="patient_feedback"
                    :rows="3"
                    :placeholder="__('Отзыв о пациенте')"
                    :label="__('Отзыв о пациенте')" />
            </el-col>
        </el-row>
        <div class="form-footer text-right">
            <el-button
                v-if="$can('doctor-cabinet.signal-records')"
                type="default"
                @click="save">
                {{ __('Сохранить') }}
            </el-button>
            <el-button
                :disabled="model.isNew()"
                type="primary"
                @click="close">
                {{ __('Ознакомился, закрыть') }}
            </el-button>
        </div>
    </model-form>
</template>

<script>
export default {
    props: {
        model: Object,
    },
    data() {
        return {
            pickerOptions: {
                disabledDate: this.checkDisabledDate,
                firstDayOfWeek: 1,
            }
        }
    },
    methods: {

        save() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$info(__('Информация была успешно сохранена'));
                this.close();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        checkDisabledDate (date) {
            return this.$moment(date).isAfter(this.$moment(), "day");
        },
        close() {
            this.$emit('close');
        }
    },
    watch: {
        'model.onco_observation_gyn': function (newValue, oldValue) {
            if(!newValue){
                this.model.onco_observation_gyn_date = null;
            }
        },
        'model.onco_observation_pro': function (newValue, oldValue) {
            if(!newValue){
                this.model.onco_observation_pro_date = null;
            }
        },
        'model.onco_observation_uro': function (newValue, oldValue) {
            if(!newValue){
                this.model.onco_observation_uro_date = null;
            }
        },
        'model.onco_observation_ren': function (newValue, oldValue) {
            if(!newValue){
                this.model.onco_observation_ren_date = null;
            }
        },
        'model.onco_observation_vil': function (newValue, oldValue) {
            if(!newValue){
                this.model.onco_observation_vil_date = null;
            }
        },
        'model.onco_observation_vaserman': function (newValue, oldValue) {
            if(!newValue){
                this.model.onco_observation_vaserman = null;
            }
        },
    },
}
</script>
