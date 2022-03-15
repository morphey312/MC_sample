<template>
    <accounts-list
        ref="table"
        :filters="initialFilters"
        @selection-changed="setActiveItem"
        @header-filter-updated="syncFilters"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$can('patient-users.update')"
                :disabled="activeItem === null"
                @click="findPatient">
                {{ (activeItem && activeItem.patient) ? __('Привязать другого пациента') : __('Привязать профиль пациента') }}
            </el-button>
            <el-button
                v-if="$can('patient-users.delete')"
                :disabled="activeItem === null"
                @click="remove">
                {{ __('Удалить аккаунт ЛК') }}
            </el-button>
            <el-popover
                v-if="$can('patient-users.password-reset')"
                placement="top-start"
                trigger="click">
                <div>
                    <a href="#" @click.prevent="passwordReset">{{ __('Сбросить пароль') }}</a>
                </div>
                <el-button
                    slot="reference"
                    :disabled="activeItem === null">
                    {{ __('Еще') }}
                </el-button>
            </el-popover>
        </div>
    </accounts-list>
</template>

<script>
import AccountsList from './accounts/List.vue';
import ManageMixin from '@/mixins/manage';
import SearchPatient from '@/components/patients/search/Search.vue';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        AccountsList,
    },
    props: {
        initialFilters: Object,
    },
    methods: {
        findPatient() {
            let account = this.activeItem;
            this.$modalComponent(SearchPatient, {}, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, patient) => {
                    this.bindExisting(account, patient);
                    dialog.close();
                },
            }, {
                header: __('Привязка пациента'),
                width: '1270px',
            });
        },
        bindExisting(account, patient) {
            let message;
            let patient2 = this.formatPatient(patient);
            if (account.patient) {
                let patient1 = this.formatPatient(account.patient);
                message = __('Вы уверены, что хотите заменить профиль пациента в аккаунте ЛК с <b>{patient1}</b> на <b>{patient2}</b>', {patient1, patient2});
            } else {
                message = __('Вы уверены, что хотите присоединить аккаунт ЛК <b>{number}</b> к профилю <b>{patient2}</b>', {number: account.phone, patient2});
            }
            this.$confirm(message, () => {
                account.patient_id = patient.id;
                account.save().then(() => {
                    this.$info(__('Привязка к профилю пациента успешно обновлена'));
                    this.refresh();
                }).catch((error) => {
                    let response = ((error.response || {}).response || {});
                    let errors = this.$getValidationError(response, 'patient_id');
                    if (errors) {
                        this.$error(__('Выбраный пациент уже привязан к другому аккаунту ЛК'));
                    } else {
                        this.$error(__('Не удалось обновить привязку к профилю пациента'));
                    }
                });
            });
        },
        formatPatient(patient) {
            return __('{name}, тел. {phone}', {name: patient.full_name, phone: this.$formatter.phoneNumberFormat(patient.primary_phone_number)});
        },
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот аккаунт ЛК?'),
                deleted: __('Аккаунт ЛК был успешно удален'),
            };
        },
    },
}
</script>
