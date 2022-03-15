<template>
    <model-form :model="model">
        <slot name="header"></slot>
        <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
            <el-tab-pane
                :lazy="true"
                :label="__('Данные о пациенте')"
                name="personal-info" >
                <section>
                    <personal-info
                        :model="model"
                        :is-patient="isPatient" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="isPatient && $can('patient-cards.access')"
                :lazy="true"
                :label="__('Карты')"
                name="cards" >
                <patient-cards
                    :patient="model"
                    @add-card="addPatientCard"
                    @delete-card="deletePatientCard" />
            </el-tab-pane>
            <el-tab-pane
                v-if="isPatient && $can('patient-discount-cards.access')"
                :lazy="true"
                :label="__('Дисконтные карты')"
                name="discount-cards" >
                <discount-cards
                    :patient="model"
                    @add-card="addDiscountCard"
                    @update-card="updateDiscountCard"
                    @delete-card="deleteDiscountCard" />
            </el-tab-pane>
            <el-tab-pane
                v-if="isPatient && $can('patient-relations.access')"
                :lazy="true"
                :label="__('Родственники')"
                name="relatives" >
                <relatives
                    :patient="model"
                    @add-relative="addRelative"
                    @update-relative="updateRelative"
                    @delete-relative="deleteRelative" />
            </el-tab-pane>
            <el-tab-pane
                v-if="isPatient && hasInsurance && $can('insurance-policies.access')"
                :lazy="true"
                :label="__('Страховые полисы')"
                name="insurance-policies" >
                <insurance-policies
                    :patient="model"
                    @add-policy="addPolicy"
                    @update-policy="updatePolicy"
                    @delete-policy="deletePolicy" />
            </el-tab-pane>
            <el-tab-pane
                :lazy="true"
                :label="__('Дополнительно')"
                name="additional-info" >
                <section>
                    <additional-info
                        :model="model" />
                </section>
            </el-tab-pane>
        </el-tabs>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import PersonalInfo from './form-tabs/PersonalInfo.vue';
import AdditionalInfo from './form-tabs/AdditionalInfo.vue';
import PatientCards from './form-tabs/CardList.vue';
import DiscountCards from './form-tabs/DiscountCards.vue';
import Relatives from './form-tabs/Relatives.vue';
import InsurancePolicies from './form-tabs/InsurancePolicies.vue';
import PatientCard from '@/models/patient/card/card';
import CONSTANTS from '@/constants';

export default {
    props: {
        model: {
            type: Object
        },
    },
    components: {
        PersonalInfo,
        AdditionalInfo,
        PatientCards,
        DiscountCards,
        Relatives,
        InsurancePolicies,
    },
    computed: {
        isPatient() {
            return this.model.status === CONSTANTS.PATIENT.STATUS.PATIENT;
        },
        hasInsurance() {
            return this.model.med_insurance === CONSTANTS.PATIENT.MED_INSURANCE.HAS_INSURANCE;
        },
    },
    data() {
        return {
            activeTab: 'personal-info',
        };
    },
    methods: {
        addPatientCard(card) {
            let clinicCard = this.findClinicCard(card);

            if (clinicCard) {
                card._parent = clinicCard;
                clinicCard.specializations.push(card);
            } else {
                let clinicCard = new PatientCard({
                    clinic_id: card.clinic_id,
                    patient_id: this.model.id,
                    specializations: [card],
                });
                this.model.cards.push(clinicCard);
            }
            return;
        },
        deletePatientCard(card) {
            let clinicCard = this.findClinicCard(card);
            let index = _.findIndex(clinicCard.specializations, {specialization_id: card.specialization_id});
            clinicCard.specializations.splice(index, 1);
            this.$info(__('Карта была успешно удалена'));
        },
        findClinicCard(card) {
            return this.model.cards.find(item => item.clinic_id == card.clinic_id);
        },
        addDiscountCard(card) {
            this.model.issued_discount_cards.push(card);
        },
        updateDiscountCard(card) {
            let discountCard = this.model.issued_discount_cards.find(item => {
                return item.id == card.id;
            });

            if (discountCard) {
                discountCard.set(card._attributes);
            }
        },
        deleteDiscountCard(card) {
            let cardIndex = this.model.issued_discount_cards.findIndex(item => {
                if (!card.isNew()) {
                    return item.id == card.id;
                }

                return card.discount_card_type_id == item.discount_card_type_id &&
                       card.clinic_id == item.clinic_id &&
                       card.valid_from == item.valid_from &&
                       card.expires == item.expires;
            });

            if (cardIndex !== -1) {
                this.model.issued_discount_cards.splice(cardIndex, 1);
                this.$info(__('Карта была успешно удалена'));
            }
        },
        addRelative(relative) {
            this.model.relatives.push(relative);
        },
        updateRelative(relative) {
            let patientRelative = this.model.relatives.find(item => item.id == relative.id);

            if (patientRelative) {
                patientRelative.set(relative._attributes);
            }
        },
        deleteRelative(relative) {
            let relativeIndex = this.model.relatives.findIndex(item => item.id == relative.id);
            if (relativeIndex !== -1) {
                this.model.relatives.splice(relativeIndex, 1);
                this.$info(__('Родственное отношение успешно удалено'));
            }
        },
        addPolicy(policy) {
            this.model.insurance_policies.push(policy);
        },
        updatePolicy(policy) {
            let insurancePolicy = this.model.insurance_policies.find(item => {
                return item.id == policy.id;
            });

            if (insurancePolicy) {
                insurancePolicy.set(policy.attributes);
            }
        },
        deletePolicy(policyIndex) {
            if (policyIndex !== null) {
                this.model.insurance_policies.splice(policyIndex, 1);
                this.$info(__('Полис был успешно удален'));
            }
        },
    },
};
</script>
