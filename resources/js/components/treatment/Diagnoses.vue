<template>
    <page
        :title="__('Диагнозы')"
        type="flex">
        <section class="grey-cap shrinkable">
            <diagnosis-list
                ref="table"
                @loaded="refreshed" >
            </diagnosis-list>
        </section>
    </page>
</template>

<script>
import DiagnosisList from './diagnoses/List.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        DiagnosisList,
    },
};
</script>