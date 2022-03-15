<template>
    <span 
        v-if="data._is_group"
        class="expandable-item"
        @click.stop="toggleGroup(data.group_id)">
        <span :class="['caret', {toggled: data._is_expanded}]" />
        {{ data[property] }}
    </span>
    <span 
        v-else
        class="expandable-item child">
        {{ data[property] }}
    </span>
</template>

<script>
export default {
    props: {
        data: Object,
        property: {
            type: String,
            default: 'value',
        },
    },
    methods: {
        toggleGroup(groupId) {
            this.$emit('toggle', groupId);
        },
    },
};
</script>