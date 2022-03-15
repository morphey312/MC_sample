<template>
    <div class="card-record-subsection">
        <h2 v-if="label">{{ label }}</h2>
        <template v-for="(field, index) in fields">
            <sub-section 
                v-if="isSection(field)"
                :key="index"
                :label="field.label"
                :fields="field.children"
                :readonly="readonly"
                v-on="$listeners" />
            <form-line 
                v-else
                :key="index"
                :fields="isLine(field) ? field.children : [field]"
                :readonly="readonly"
                v-on="$listeners" />
        </template>
    </div>
</template>

<script>
import SectionMixin from './mixins/section';
import SubSection from './SubSection.vue';
import Wrapper from '../SectionWrapper.vue';

export default {
    mixins: [
        SectionMixin,
    ],
    components: {
        Wrapper,
        SubSection,
    },
    props: {
        fields: Array,
        label: String,
        readonly: {
            type: Boolean,
            default: false,
        },
    },
};
</script>