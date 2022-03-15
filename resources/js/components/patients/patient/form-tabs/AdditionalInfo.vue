<template>
    <el-row :gutter="20">
        <el-col :span="8">
            <form-input
                :entity="model"
                property="firstname_latin"
                :label="__('Имя как в загран. паспорте')" />
            <form-row name="for_has_entity">
                <el-checkbox
                    v-model="hasLegalEntity"
                    :label="__('Сотрудник корп. клиента')" />
            </form-row>
            <form-select
                v-show="hasLegalEntity"
                :entity="model"
                :repository="legalEntities"
                :clearable="true"
                :min-query-len="0"
                property="legal_entity_id"
                :label="__('Корп. клиент')" />
            <form-checkbox
                :entity="model"
                property="is_attention"
                :label="__('Метка «Внимание!»')" />
            <form-text
                v-show="model.is_attention"
                :entity="model"
                property="attention_comment"
                :label="__('Комментарий')" />
            <div class="label-wrapper">
                <label class="input-label">
                    <span>
                        {{ __('ID пациента:') }}
                    </span>
                </label>
                <div>{{ model.id }}</div>
            </div>
            <div>
                <label class="input-label">
                    <span>
                        {{ __('ID клиента:') }}
                    </span>
                </label>
                <div v-for="client_id in model.client_ids">{{ client_id.value }}</div>
            </div>
        </el-col>
        <el-col :span="8">
            <form-input
                :entity="model"
                property="lastname_latin"
                :label="__('Фамилия как в загран. паспорте')" />
            <form-checkbox
                :entity="model"
                property="has_registration"
                :label="__('Зарегистрировать ЛК')"
                :disabled="registrationDisabled" />
            <form-checkbox
                :entity="model"
                property="is_confirmed"
                :label="__('Личность подтверждена')"
                :disabled="!$can('patients.confirm')" />
            <form-checkbox
                :entity="model"
                property="black_mark"
                :label="__('Черная метка')" />
            <form-select
                v-show="model.black_mark"
                :entity="model"
                options="black_mark_reason"
                property="black_mark_reason"
                :label="__('Причина')" />
            <form-text
                v-show="model.black_mark"
                :entity="model"
                property="black_mark_comment"
                :label="__('Комментарий')" />
            <div v-if="canPatientCabinetAccess">
                <div style="font-weight: bold; margin-bottom: 10px;">{{ __('Перейти в ЛК') }}</div>
                <a
                    :href="`https://mycabinet.info/#/auth/staff-login?phone=${model.personal_cabinet_phone}`"
                    style="font-weight: bold"
                    target="_blank">https://mycabinet.info/#/auth/staff-login
                </a>
            </div>
        </el-col>
        <el-col :span="8">
            <form-input
                :entity="model"
                property="passport_no"
                :label="__('Номер загран. паспорта')" />
            <form-checkbox
                :entity="model"
                property="mailing"
                :label="__('Подписан на рассылку')" />
            <form-checkbox
                :entity="model"
                property="sms"
                :label="__('Подписан на SMS рассылку')" />
            <form-checkbox
                :entity="model"
                property="mailing_analysis"
                :label="__('Отправлять результаты анализов на e-mail')" />
            <form-checkbox
                :entity="model"
                property="is_skk"
                :label="__('Обращение в СКК')" />
            <form-select
                v-show="model.is_skk"
                :entity="model"
                options="skk_reason"
                property="skk_reason"
                :label="__('Причина')" />
            <form-text
                v-show="model.is_skk"
                :entity="model"
                property="skk_comment"
                :label="__('Комментарий')" />
        </el-col>
    </el-row>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import LegalEntityRepository from '@/repositories/legal-entity';

export default {
    props: {
        model: {
            type: Object
        },
        isPatient: {
            type: Boolean,
        },
    },
    computed: {
        registrationDisabled() {
            return _.isFilled(this.model.registration_id)
                || this.model.user_id !== null;
        },
        canPatientCabinetAccess() {
            return this.model.personal_cabinet_phone !== null && this.$can('patient-cabinet.staff-access');
        },
    },
    data() {
        return {
            legalEntities: new ProxyRepository(({filters}) => {
                let combined = this.legalEntities.getFilters(filters);
                let repository = new LegalEntityRepository();
                if ((combined.filters.query === undefined || combined.filters.query.length === 0) &&
                    combined.filters.clinic.length === 0) {
                    return Promise.resolve([]);
                }
                return repository.fetchList(combined.filters);
            }, {
                filters: this.getLegalEntitiesFilters(),
            }),
            hasLegalEntity: _.isFilled(this.model.legal_entity_id),
        };
    },
    methods: {
        isFilled(val) {
            return _.isFilled(val);
        },
        getLegalEntitiesFilters() {
            return {
                clinic: this.model.clinics,
            };
        },
    },
    watch: {
        hasLegalEntity(val) {
            if (!val) {
                this.model.legal_entity_id = null;
            }
        },
    },

}
</script>
