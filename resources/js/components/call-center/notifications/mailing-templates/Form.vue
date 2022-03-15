<template>
    <model-form :model="model">
        <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :label="__('Параметры')"
                name="general">
                <section>
                    <el-row :gutter="20">
                        <el-col :span="8">
                            <form-input
                                :entity="model"
                                property="name"
                                :label="__('Название шаблона')" />
                            <form-select
                                :entity="model"
                                options="notification_mailing_scenario"
                                property="scenario"
                                :label="__('Сценарий')" />
                        </el-col>
                        <el-col :span="8">
                            <form-select
                                :entity="model"
                                :options="providers"
                                property="channel_id"
                                :label="__('Провайдер')" />
                            <form-select
                                v-if="isMissedFirstAppointment"
                                :entity="model"
                                :options="statuses"
                                :limit="1"
                                property="statuses"
                                :multiple="true"
                                :filterable="true"
                                :label="__('Причина отсутствия')" />
                        </el-col>
                        <el-col :span="8">
                            <form-checkbox
                                :entity="model"
                                property="schedule_mailing"
                                :label="__('Рассылка по расписанию')"
                                css-class="aligned-checkbox" />
                            <form-checkbox
                                :entity="model"
                                property="disabled"
                                :label="__('Не использовать')"
                                css-class="aligned-checkbox" />
                        </el-col>
                    </el-row>
                </section>
                <hr />
            </el-tab-pane>
            <el-tab-pane
                :label="__('Настройки')"
                name="settings_ms"
            >
                <section>
                    <mailing-settings-clinics
                        :notification-mailing-template="model"
                    >
                    </mailing-settings-clinics>
                </section>
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import NotificationMailingProviderRepository from '@/repositories/notification/mailing-provider';
import ClinicRepository from '@/repositories/clinic';
import SpecializationRepository from '@/repositories/specialization';
import CONSTANTS from '@/constants';
import PasteSpecial from './Insert.vue';
import MailingSettingsClinics from './settings/MailingClinics.vue';
import ReasonRepository from "../../../../repositories/appointment/status/reason";

export default {
    components: {
        PasteSpecial,
        MailingSettingsClinics
    },
    props: {
        model: Object,
    },
    data() {
        return {
            activeTab: 'general',
            parents: [],
            providers: new NotificationMailingProviderRepository(),
            statuses: new ReasonRepository(),
            specializations: new SpecializationRepository(),
            clinics: new ClinicRepository(),
            CONSTANTS: CONSTANTS,
            today: this.$moment().format('YYYY-MM-DD'),
        };
    },
    mounted() {
    },
    computed:{
        isMissedFirstAppointment(){
            return this.model.scenario === CONSTANTS.NOTIFICATION_MAILING_TEMPLATE.SCENARIO.MISSED_FIRST_APPOINTMENTS;
        },
    },
    watch: {
    },
    methods: {
    }
}
</script>
