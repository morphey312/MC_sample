<template>
    <div class="page">
        <div :class="`${type}-content`">
            <div class="page-header">
                <router-link
                    v-if="backRoute !== null"
                    :to="backRoute"
                    class="nav-back">
                    <svg-icon 
                        name="arrow-left-alt" 
                        class="icon-blue icon-large" />
                </router-link>
                <div class="heading-wrapper">
                    <breadcrumbs 
                        v-if="breadcrumbs.length !== 0"
                        :items="breadcrumbs" />
                    <div class="heading">
                        <h1>{{ title }}</h1>
                        <slot name="header-addon" />
                    </div>
                </div>
            </div>
            <div :class="contentClass">
                <slot />
            </div>
        </div>
    </div>
</template>

<script>
import Breadcrumbs from './page/Breadcrumbs.vue';

export default {
    components: {
        Breadcrumbs,
    },
    props: {
        title: String,
        type: {
            type: String,
            default: 'page',
        },
        contentClass: {
            type: String,
            default: 'content-wrapper'
        },
        breadcrumbs: {
            type: Array,
            default: () => [],
        },
        backRoute: {
            type: Object,
            default: null,
        },
    },
    mounted() {
        this.scrollToTop();
        this.setTitle(this.title);
    },
    beforeDestroy() {
        this.setTitle(null);
    },
    methods: {
        scrollToTop() {
            window.scrollTo(0,0);
        },
        setTitle(to) {
            window.document.title = window.appConfig.name + (to ? ` :: ${to}` : '');
        },
    },
};
</script>