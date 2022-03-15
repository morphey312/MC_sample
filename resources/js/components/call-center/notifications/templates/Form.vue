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
                                options="notification_scenario"
                                property="scenario"
                                :label="__('Сценарий')" />
                            <form-select
                                v-if="isAppointmentPreparation"
                                :entity="model"
                                :options="clinics"
                                :limit="1"
                                property="clinics"
                                :multiple="true"
                                :filterable="true"
                                :label="__('Клиника')"
                            />
                        </el-col>
                        <el-col :span="8">
                            <form-select
                                :entity="model"
                                :options="parents"
                                property="parent_id"
                                :label="__('Родительский шаблон')" />
                            <form-input
                                v-if="scenarioCategory === 'email'"
                                :entity="model"
                                property="subject"
                                :label="__('Тема сообщения')" />
                            <form-time
                                v-if="scenarioCategory === 'sms' || scenarioCategory === 'telegram'"
                                :entity="model"
                                :disabled="isManualScenario"
                                property="time"
                                :label="__('Время отправки')"
                                mode="picker"
                            />
                            <form-select
                                v-if="isAppointmentPreparation"
                                :entity="model"
                                :repository="specializations"
                                property="specialization_id"
                                :min-query-len="0"
                                :label="__('Специализация')" />
                        </el-col>
                        <el-col :span="8">
                            <form-select
                                :entity="model"
                                :options="channels"
                                property="channel_id"
                                :label="__('Канал связи')" />
                            <form-checkbox
                                :entity="model"
                                property="disabled"
                                :label="__('Не использовать')"
                                css-class="aligned-checkbox" />
                            <form-select
                                v-if="isAppointmentPreparation"
                                style="margin-top: 22px;"
                                :entity="model"
                                property="service_id"
                                :repository="services"
                                :min-query-len="0"
                                :label="__('Услуга')"
                            />
                        </el-col>
                    </el-row>
                </section>
                <hr />
                <template v-if="scenarioCategory === 'telegram'">
                    <section>
                        <form-row name="roles">
                            <transfer-table
                                :items="positions"
                                v-model="model.positions"
                                :limit-selected="1"
                                :left-title="__('Должности')"
                                left-width="240px"
                                :right-title="__('Выбраные должности')"
                                right-width="240px">
                            </transfer-table>
                        </form-row>
                    </section>
                </template>
                <template v-else>
                    <section v-if="!isAppointmentPreparation && !isСallCenterQueue">
                        <form-row name="clinics">
                            <transfer-table
                                :items="clinics"
                                v-model="model.clinics"
                                :limit-selected="1"
                                :left-title="__('Клиники')"
                                left-width="240px"
                                :right-title="__('Выбраные клиники')"
                                right-width="240px">
                            </transfer-table>
                        </form-row>
                    </section>
                    <section v-if="isСallCenterQueue">
                         <form-row name="voip_queue">
                            <transfer-table
                                :items="voip_queue"
                                v-model="model.voip_queue"
                                :limit-selected="1"
                                :left-title="__('Очереди')"
                                left-width="240px"
                                :right-title="__('Выбраные очереди')"
                                right-width="240px">
                            </transfer-table>
                        </form-row>
                    </section>
                </template>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Сообщение')"
                name="body">
                <section>
                    <form-checkbox
                        :entity="model"
                        property="inherit_body"
                        :label="__('Наследовать родительский шаблон')" />
                    <form-row name="message_body">
                        <wysiwyg
                            v-if="!['sms', 'telegram'].includes(scenarioCategory)"
                            v-model="model.body"
                            ref="ebody"
                            :placeholder="__('Введите текст сообщения')"
                            :disabled="model.inherit_body" />
                        <el-input
                            v-else
                            type="textarea"
                            :autosize="{ minRows: 2 }"
                            :disabled="model.inherit_body"
                            :placeholder="__('Введите текст')"
                            v-model="model.body"></el-input>
                    </form-row>
                    <paste-special :editor="$refs.ebody" v-if="scenarioCategory !== 'telegram'" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Шапка')"
                name="header"
                v-if="scenarioCategory !== 'telegram'">
                <section>
                    <form-checkbox
                        :entity="model"
                        property="inherit_header"
                        :label="__('Наследовать родительский шаблон')" />
                    <form-row name="message_header">
                        <wysiwyg
                            v-if="scenarioCategory !== 'sms'"
                            v-model="model.header"
                            ref="eheader"
                            :placeholder="__('Введите текст в шапке сообщения')"
                            :disabled="model.inherit_header" />
                        <el-input
                            v-else
                            type="textarea"
                            :autosize="{ minRows: 2 }"
                            :disabled="model.inherit_header"
                            :placeholder="__('Введите текст')"
                            v-model="model.header"></el-input>
                    </form-row>
                    <paste-special :editor="$refs.eheader" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Настройки')"
                name="settings_s"
                v-if="scenarioCategory === 'telegram'">
                <section>
                    <settings-clinics
                        :notification-template="model"
                    >
                    </settings-clinics>
                </section>
            </el-tab-pane>
            <el-tab-pane
                :label="__('Подвал')"
                name="footer"
                v-if="scenarioCategory !== 'telegram'">
                <section>
                    <form-checkbox
                        :entity="model"
                        property="inherit_footer"
                        :label="__('Наследовать родительский шаблон')" />
                    <form-row name="message_footer">
                        <wysiwyg
                            v-if="scenarioCategory !== 'sms'"
                            v-model="model.footer"
                            ref="efooter"
                            :placeholder="__('Введите текст в подвале сообщения')"
                            :disabled="model.inherit_footer"  />
                        <el-input
                            v-else
                            type="textarea"
                            :autosize="{ minRows: 2 }"
                            :placeholder="__('Введите текст')"
                            :disabled="model.inherit_footer"
                            v-model="model.footer"></el-input>
                    </form-row>
                    <paste-special :editor="$refs.eheader" />
                </section>
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import NotificationChannelRepository from '@/repositories/notification/channel';
import NotificationTemplateRepository from '@/repositories/notification/template';
import ClinicRepository from '@/repositories/clinic';
import ServiceRepository from '@/repositories/service';
import SpecializationRepository from '@/repositories/specialization';
import CONSTANTS from '@/constants';
import PasteSpecial from './Insert.vue';
import SettingsClinics from './settings/Clinics.vue';
import PositionRepository from "@/repositories/employee/position";

export default {
    components: {
        PasteSpecial,
        SettingsClinics
    },
    props: {
        model: Object,
    },
    data() {
        return {
            activeTab: 'general',
            parents: [],
            channels: new NotificationChannelRepository(),
            services: new ServiceRepository(),
            specializations: new SpecializationRepository(),
            positions: new PositionRepository(),
            clinics: new ClinicRepository(),
            CONSTANTS: CONSTANTS,
            today: this.$moment().format('YYYY-MM-DD'),
            voip_queue: [],

        };
    },
    mounted() {
        this.fetchParents();
        this.fetchViopQueue();
    },
    computed:{
        scenarioCategory(){
            if(this.model.scenario){
                if (this.model.scenario.includes('sms_') || this.model.scenario === CONSTANTS.NOTIFICATION_TEMPLATE.SCENARIO.PREPARATION) {
                    return 'sms';
                }

                if (this.model.scenario.includes('telegram_')) {
                    return 'telegram';
                }

                return 'email';
            }

            return null;
        },
        isManualScenario(){
            if(this.model.scenario){
                return [CONSTANTS.NOTIFICATION_TEMPLATE.SCENARIO.PREPARATION,
                    CONSTANTS.NOTIFICATION_TEMPLATE.SCENARIO.MANUAL
                ].indexOf(this.model.scenario) !== -1;
            }

            return false;
        },
        isAppointmentPreparation(){
            return this.model.scenario === CONSTANTS.NOTIFICATION_TEMPLATE.SCENARIO.PREPARATION;
        },
        isСallCenterQueue(){
            return this.model.scenario === CONSTANTS.NOTIFICATION_TEMPLATE.SCENARIO.SMS_MISSED_CALL;
        }
    },
    watch: {
        ['model.specialization_id'](val) {
            this.clinics.setFilters(this.getClinicsFilters());
            this.services.setFilters(this.getServicesFilters());
        },
        ['model.clinics'](val) {
            this.services.setFilters(this.getServicesFilters());
            this.specializations.setFilters(this.getSpecializationFilters());
        },
    },
    methods: {
        fetchParents() {
            let repository = new NotificationTemplateRepository();
            repository.fetchList({is_root: 1}).then((result) => {
                this.parents = result.filter(t => t.id !== this.model.id);
            });
        },
        getSpecializationFilters() {
            return _.onlyFilled({
                status: 1,
                clinic: this.model.clinics,
            });
        },
        getServicesFilters() {
            return _.onlyFilled({
                specialization: this.model.specialization_id,
                clinic: this.model.clinics,
                disabled: false,
                has_price : {
                    from: this.today,
                    to: this.today,
                    set: 'base'
                }
            });
        },
        getClinicsFilters() {
            return _.onlyFilled({
                has_specialization: this.model.specialization_id,
            });
        },
        fetchViopQueue() {
            this.voip_queue = this.$handbook.getOptions('voip_queue').map(s => {
                let exists = this.model.voip_queue.find(type => type.queue === s.value);
                return {
                        id: exists ? exists.id : s.id,
                        value: s.value,
                    };
            })
        },
    }
}
</script>
