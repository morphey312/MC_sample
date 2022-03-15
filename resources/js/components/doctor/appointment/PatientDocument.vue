<template>
    <div
        v-if="records.length !== 0"
        class="paragraph">
        <h3 v-if="title">
            {{ title }}
        </h3>
        <document-list
            @document-deleted="documentDeleted"
            :documents="records"
            :readonly="readonly" />
    </div>
</template>
<script>
import DocumentList from './documents/List.vue';

export default {
    components: {
        DocumentList,
    },
    props: {
        records: {
            type: Array,
            default: () => [],
        },
        title: {
            type: [Boolean, String],
            default: __('Добавлены документы'),
        },
        readonly: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        documentDeleted(document) {
            this.$emit('document-deleted', document);
        },
    },
};
</script>
