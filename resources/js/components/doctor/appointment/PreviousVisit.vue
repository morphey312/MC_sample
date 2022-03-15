<template>
    <div class="previous-visit-block">
        <h2>
            {{ $formatter.dateFormat(data.appointment.date) }}
            / 
            {{ __('Врач:') }} {{ data.appointment.doctor.name }}
        </h2>
        <visit-entries
            :records="data.records"
            class="paragraph" />
        <div class="paragraph text-center">
            <el-button
                type="primary"
                @click="patientHistory">
                {{ __('История пациента') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import ModalsMixin from './mixins/modals';
import VisitEntries from './previous-visit/Entries.vue';

export default {
    mixins: [
        ModalsMixin,
    ],
    components: {
        VisitEntries,
    },
    props: {
        data: Object,
        appointment: Object,
    },
}   
</script>