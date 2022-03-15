<template>
    <div class="buttons">
        <a 
            v-if="$can('patient-cabinet.outpatient-cards')"
            href="#"
            @click.prevent="print">
            <svg-icon name="report" class="icon-small icon-blue">
                {{ __('Печатать') }}
            </svg-icon>
        </a>
    </div>
</template>
<script>
export default {
    methods: {
        print() {
            this.$emit('print');
        }
    }
}
</script>