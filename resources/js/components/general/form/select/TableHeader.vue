<template>
    <div class="select-table-header">
        <div 
            v-for="field in fields"
            class="cell"
            :style="{width: field.width}">
            {{ field.label }}
        </div>
    </div>
</template>

<script>
export default {
    props: {
        fields: Array,
    },
}
</script>