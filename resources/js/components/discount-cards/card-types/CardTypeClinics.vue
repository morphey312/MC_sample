<template>
    <div class="has-icon">
        <span class="ellipsis">
            {{ clinic_names }}    
        </span>
        <template v-if="hasClinics">
            <svg-icon 
                name="info-alt" 
                class="icon-tiny icon-grey"
                @click.stop="showDetails" />
        </template>
    </div>
</template>

<script>
import ClinicDetails from './ClinicDetails.vue';
import DetailButtonEdit from './DetailButtonEdit.vue';

export default {
    props: {
        model: Object,
    },
    computed: {
        hasClinics() {
            return this.model.clinics.length !== 0;
        },
        clinic_names() {
            if (!this.hasClinics) {
                return '';
            }
            return this.$formatter.listFormat(this.model.clinics, 'clinic_name');
        },
    },
    methods: {
        showDetails(cardType) {
            this.$modalComponent(ClinicDetails, {
                model: this.model,
            }, {}, {
                header: "Клиники карты '" + this.model.name + "'",
                width: '600px',
                customClass: 'no-footer',
                headerAddon: {
                    component: DetailButtonEdit,
                    eventListeners: {
                        click: (dialog) => {
                            dialog.close();
                            this.$emit('edit-card-type', this.model);
                        }
                    }
                },
            });
        },
    },
}   
</script>