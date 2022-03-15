<template>
    <div class="card-record-subsection">
        <h3>{{ label }}</h3>
        <form-line 
            v-for="(field, index) in fields"
            :key="index"
            :fields="isLine(field) ? field.children : [field]" />
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
    },
};
</script>