<template>
    <episode-form :model="model">
        <div 
            slot="buttons" 
            class="form-footer text-right">
            <el-button 
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
            <el-button
                type="primary"
                @click="createAndSubmit">
                {{ __('Создать и отправить в e-Health') }}
            </el-button>
        </div>
    </episode-form>
</template>

<script>
import EpisodeForm from './Form.vue';
import CareEpisode from '@/models/ehealth/care-episode';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        EpisodeForm,
    },
    props: {
        appointment: Object,
    },
    data() {
        return {
            model: new CareEpisode({
                patient_id: this.appointment.patient_id,
                name: this.guessName(this.appointment),
                date_start: this.$moment().format('YYYY-MM-DD'),
            }),
        }
    },
    methods: {
        guessName(appointment) {
            let base = _.find(appointment.services, (service) => service.is_base);
            if (base !== undefined) {
                return base.name;
            }
            return __('Осмотр врача, {specialization}', {specialization: appointment.specialization_name});
        },
        createAndSubmit() {

        },
    },
}
</script>