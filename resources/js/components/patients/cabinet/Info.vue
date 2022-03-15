<template>
    <page :title="__('Информация о пациенте: {name}', {name: patient.full_name})">
        <template slot="header-addon">
            <div class="buttons">
                <a
                    href="#"
                    @click.prevent="showSignalRecord">
                    <svg-icon 
                        name="report-alt" 
                        class="icon-small icon-blue">
                        {{ __('Сигнальные обозначения') }}
                    </svg-icon>
                </a>
            </div>
        </template>
        <div class="patient-info">
            <patient-form :model="patient">
                <div
                    v-if="$canUpdate('patients')"
                    slot="buttons"
                    class="sticky-footer buttons text-right">
                    <el-button
                        type="primary"
                        @click="save">
                        {{ __('Сохранить') }}
                    </el-button>
                </div>
            </patient-form>
        </div>
    </page>
</template>

<script>
import CabinetMixin from './mixins/cabinet';
import PatientForm from '@/components/patients/patient/Form.vue';

export default {
    mixins: [
        CabinetMixin,
    ],
    components: {
        PatientForm,
    },
    methods: {
        save() {
            this.$clearErrors();
            this.patient.save().then((response) => {
                this.$info(__('Пациент был успешно обновлен'));
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },
};
</script>