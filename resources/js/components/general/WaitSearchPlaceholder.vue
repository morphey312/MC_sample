<template>
    <div 
        class="wait-search-placeholder">
        <div class="circle">
            <img :src="image" alt="" />
            {{ message }}
            <template v-if="canCreate">
                {{ __('или') }}
                <a 
                    href="#" 
                    @click.prevent="$emit('create')">
                    {{ createText }}
                </a>
            </template>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        image: {
            type: String,
            default: '/svg/content/img-wait-search.svg',
        },
        message: {
            type: String,
            default: __('Задайте критерии поиска'),
        },
        canCreate: {
            type: Boolean,
            default: false,
        },
        createText: {
            type: String,
            default: __('добавьте запись'),
        },
    },
};
</script>