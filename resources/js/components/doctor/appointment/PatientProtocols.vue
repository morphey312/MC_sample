<template>
    <div
        v-if="records.length !== 0"
        class="paragraph">
        <h3 v-if="title">
            {{ title }}
        </h3>
        <research-list
            :research-results="records"
            :readonly="readonly"
            @research-deleted="researchDeleted" />
    </div>
</template>
<script>
import ResearchList from './research/List.vue';

export default {
    components: {
        ResearchList,
    },
    props: {
        records: {
            type: Array,
            default: () => [],
        },
        title: {
            type: [Boolean, String],
            default: __('Добавлены результаты исследований'),
        },
        readonly: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        researchDeleted(protocol) {
            this.$emit('research-result-deleted', protocol);
        }
    }
}
</script>
