<template>
    <span class="ds-input">
        <label class="prefix">D</label>
            <span class="input">{{ val }}</span>
        <label class="suffix">S</label>
    </span>
</template>
<script>
export default {
    props: {
        value: String,
    },
    data() {
        return {
            val: this.value,
        };
    },
};
</script>