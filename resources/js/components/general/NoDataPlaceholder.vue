<template>
    <div 
        class="no-data-placeholder">
        <div class="circle">
            <img :src="image" alt="" />
            {{ message }}
        </div>
    </div>
</template>

<script>
export default {
    props: {
        image: {
            type: String,
            default: '/svg/content/table-no-data.svg',
        },
        message: {
            type: String,
            default: __('Нет данных для отображения'),
        },
    },
};
</script>