<template>
    <div class="card-record-subsection">
        <h2 v-if="label">{{ label }}</h2>
        <template v-for="(field, index) in fields">
            <sub-section 
                v-if="isSection(field)"
                :key="index"
                :label="field.label"
                :fields="field.children" />
            <form-line 
                v-else
                :key="index"
                :fields="isLine(field) ? field.children : [field]" />
        </template>
    </div>
</template>

<script>
import SubSection from './SubSection.vue';
import SectionMixin from './mixins/section';

export default {
    mixins: [
        SectionMixin,
    ],
    components: {
        SubSection,
    },
    props: {
        fields: Array,
        label: String,
    },
};
</script>