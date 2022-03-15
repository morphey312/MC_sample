<template>
    <div class="select-table-row">
        <div 
            v-for="field in fields"
            class="cell"
            :style="{width: field.width}"
            :title="renderField(field)"
            v-html="renderField(field)" />
    </div>
</template>

<script>
export default {
    props: {
        fields: Array,
        option: Object,
    },
    methods: {
        renderField(field) {
            return field.formatter 
                ? field.formatter(this.option[field.name]) 
                : this.option[field.name];
        },
    },
}
</script>