<template>
    <div class="card-record-subsection">
        <h3>{{ label }}</h3>
        <form-line 
            v-for="(field, index) in fields"
            :key="index"
            :fields="isLine(field) ? field.children : [field]"
            :readonly="readonly"
            v-on="$listeners" />
    </div>
</template>

<script>
import SectionMixin from './mixins/section';

export default {
    mixins: [
        SectionMixin,
    ],
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