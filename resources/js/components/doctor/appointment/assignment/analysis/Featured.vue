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
            return this.featured ? __('Убрать анализ из избранных') : __('Добавить в избранные');
        },
    },
    methods: {
        toggleFeatured() {
            this.$emit('featured-changed', this.model);
        },
        checkFeatured(result) {
            this.featured = this.featuredList.findIndex(item => {
                return item.analysis_id == this.model.analysis_id;   
            }) !== -1;  
        },
    },
    watch: {
        ['featuredList']() {
            this.checkFeatured();
        },
    }
}   
</script>