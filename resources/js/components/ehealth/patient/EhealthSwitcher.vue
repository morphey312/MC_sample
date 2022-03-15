<template>
	<div class="mr-20">
        <el-switch
            v-model="ehealthRegistration"
            :disabled="ehealthRegistration"
            active-color="#B7B308"
            inactive-color="#BDBDBD"
            @change="sendEmit">
        </el-switch>
        <span v-if="!hasEhealthPatient" class="ml-10">{{ __('Пациент eHealth') }}</span>
        <a v-else
            class="ml-10"
            href="#"
            @click.prevent="$emit('editEhealthPatient', ehealthPatient.id)">
            {{ __('Пациент eHealth') }}
        </a>
    </div>
</template>
<script>
export default {
    data() {
        return {
            ehealthRegistration: false,
            hasEhealthPatient: false,
            ehealthPatient: {},
        }
    },
    watch: {
        ehealthPatient(val) {
            if (val && val.id) {
                this.hasEhealthPatient = true
            }
        }
    },
	methods: {
        sendEmit() {
            this.ehealthRegistration = false;
            this.$emit('ehealthRegistration');
        },
		close() {
			this.$emit('close');
		},
        setEhealthPatient(patient) {
            this.ehealthPatient = patient;
            if (patient && patient.ehealth_id) {
                this.ehealthRegistration = true;
            }
        }
	}
}
</script>
