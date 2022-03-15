<template>
    <div>
        <div class="form-input"><b v-html="detailHeader"></b></div>
        <doctor-table 
            :doctors="limitation.doctors" 
            :disable="true"
            :row-input="false" />
    </div>
</template>

<script>
import DoctorTable from './DoctorTable.vue';

export default {
    components: {
        DoctorTable,
    },
    props: {
        limitation: {
            type: Object,
            required: true,
        },
    },
    computed: {
        detailHeader() {
            let header = __('Действует для клиники: {name}.', {name: this.limitation.clinic_name});
            header += __('Специализация: {name}.', {name: this.limitation.specialization_name});
            header += __('С {from} ‒ {to}', {from: this.formattedDate(this.limitation.date_from), to: this.formattedDate(this.limitation.date_to)});
            return header;
        },
    },
    methods: {
        formattedDate(date) {
            return this.$formatter.dateFormat(date);
        },
    }
}   
</script>