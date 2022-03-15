<template>
    <div class="breadcrumbs">
        <template v-for="(item, index) in items">
            <template v-if="index > 0">/</template>
            <router-link
                v-if="isLink(item)"
                :to="item.route">
                {{ item.label }}
            </router-link>
            <span v-else>
                {{ item }}
            </span>
        </template>
    </div>
</template>

<script>
export default {
    props: {
        items: {
            type: Array,
            required: true,
        },
    },
    methods: {
        isLink(item) {
            return _.isObject(item);
        },
    },
};
</script>