<template>
    <card-form
        ref="cardForm"
        :model="model"
        :patient="patient"
        :disabled="true" >
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </card-form>
</template>

<script>
import CardForm from './Form.vue';
import DiscountCardMixin from '@/components/patients/mixins/discount-card';

export default {
    mixins: [
        DiscountCardMixin,
    ],
    components: {
        CardForm,
    },
    props: {
        item: Object,
        patient: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
    methods: {
        update() {
            this.$clearErrors();

            this.model.validate().then((errors) => {
                if (_.isEmpty(errors)) {
                    if (this.hasAppointment()) {
                        return this.hasAppointmentError();
                    }

                    if (this.isInvalidMaxExpire()) {
                        return this.expireError();
                    }

                    if (!this.model.isNew()) {
                        if (this.isOwner()) {
                            if (this.isChangedDisabledOnFalse() && this.isNumberOfOwnersMoreThanAllowed()) {
                                this.$nextTick(() => {
                                    this.$warning(__('Перевышено количество пользователей данной картой. Активируйте карты пользователей вручную.'));
                                });
                            }
                        } else {
                            if (this.isDisabledCard()) {
                                return this.$error(__('Редактирование не возможно, данная карта неактивна у владельца.'));
                            }
                            if (this.isChangedDisabledOnFalse() && this.isAvailableToAddNewPatient()) {
                                return this.$error(__('Превышено количество пользователей данной картой. Изменение активности невозможно.'));
                            }
                        }
                    }

                    if (this.hasSameCard()) {
                        return this.crossError();
                    }

                    this.$emit('saved', this.model);
                    return;
                }

                return this.$displayErrors({errors});
            });
        },
        isOwner() {
            return this.model.owner.id === this.patient.id;
        },
        isChangedDisabledOnFalse() {
            let oldValue = this.item.patients.find(patient => patient.patient_id === this.patient.id).disabled;
            let newValue = this.model.patients.find(patient => patient.patient_id === this.patient.id).disabled;

            return oldValue === true && newValue === false;
        },
        isNumberOfOwnersMoreThanAllowed() {
            return this.model.patients.length > this.model.type.max_owners;
        },
        getOwnerCard() {
            return this.item.patients.find(patient => patient.patient_id === this.patient.id);
        },
        isDisabledCard() {
            return this.model.patients.find(patient => patient.patient_id === this.model.owner.id).disabled;
        },
        isAvailableToAddNewPatient() {
            return !(this.item.patients.filter(patient => patient.disabled === false).length < this.model.type.max_owners);
        },
        hasAppointment() {
            if (this.model.used_in_appointments) {
                let list = this.model.used_in_appointments[this.patient.id];
                if (list) {
                    return list.find(item => item.date > this.model.expires);
                }
            }
            return false;
        },
        hasAppointmentError() {
            return this.$error(__('Есть запись на прием выходящая за период действия карты'));
        },
    }
}
</script>
