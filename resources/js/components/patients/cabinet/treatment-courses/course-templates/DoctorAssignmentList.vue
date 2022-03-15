<!-- /** @scan-translations-off */ -->
<template>
    <div>
        <div style="position: absolute; top: -55555px;">
            <div class="moz-blank" id="moz-blank" style="width: 185mm; min-height: 280mm;">
                <blank-header 
                    :clinic="clinic"
                    :blank-data="blankData" />
                <div class="blank-body">
                    <div class="text-center"><h1>{{ __('ЛИСТОК ЛІКАРСЬКИХ ПРИЗНАЧЕНЬ') }}</h1></div>
                    <div class="blank-content">
                        <div class="row">
                            <div><b>{{ __('Номер медичної карти стаціонарного хворого') }}</b>&nbsp;{{ patient.card_number }}</div>
                            <div><b>{{ __('Прізвище, ім’я, по батькові хворого') }}:</b>&nbsp;{{ patient.full_name }}</div>
                        </div>
                        <div class="row">
                            <b>{{ __('Номер палати') }}:</b>&nbsp;
                        </div>
                        <assignments :treatment-activities="treatmentActivities" />
                    </div>
                </div>
            </div>
        </div>
        <section 
            v-loading="loading"
            ref="docContainer"
            id="blank-container"
            class="light-grey pdf-container">
        </section>
     </div>
</template>
<script>
import BlankHeader from './BlankHeader.vue';
import DocumentMixin from './mixin/document';
import Assignments from './Assignments.vue';

const BlankData = {
    blankNumber: '003-4/о',
    date: '29.05.2013',
    number: 435
};

export default {
    mixins: [
        DocumentMixin,
    ],
    components: {
        BlankHeader,
        Assignments,
    },
    props: {
        treatmentActivities: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            blankData: BlankData,
            outpatientRecord: null,
            documentTitle: __('ЛИСТОК_ЛІКАРСЬКИХ_ПРИЗНАЧЕНЬ') + '_№' + this.patient.card_number,
        };
    },
    mounted() {
        this.$nextTick(() => {
            this.loading = false;
            this.makePDF();
        });
    },
    
}
</script>