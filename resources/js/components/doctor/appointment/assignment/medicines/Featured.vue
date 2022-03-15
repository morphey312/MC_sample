<template>
    <col-featured 
        :tooltip-text="tooltipText"
        :featured="featured"
        @featured-changed="toggleFeatured"
    />
</template>
<script>
import ColFeatured from '@/components/general/table/ColFeatured.vue';

export default {
    components: {
        ColFeatured,
    },
    props: {
        model: Object,
        featuredList: Array,
    },
    data() {
        return {
            featured: false,
        }
    },
    mounted() {
        this.checkFeatured();
    },
    computed: {
        tooltipText() {
            return this.featured ? __('Убрать медикамент из избранных') : __('Добавить в избранные');
        },
    },
    methods: {
        toggleFeatured() {
            this.$emit('featured-changed', this.model);
        },
        checkFeatured() {
            this.featured = this.featuredList.findIndex(item => {
                return item.medicine_id == this.model.medicine_id;
            }) !== -1;
        },
    },
    watch: {
        ['featuredList']() {
            this.checkFeatured();
        },
    },
}   
</script>