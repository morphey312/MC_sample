<template>
    <div class="flex-content" style="overflow-y: auto">
        <p 
            v-for="(entry, index) in entries"
            :key="index">
            <svg-icon 
                class="icon-small"
                :name="iconType(entry.type)">
                {{ $formatter.timeFormat(entry.time) }}:
                {{ entry.message }}
            </svg-icon>
        </p>
    </div>
</template>

<script>
export default {
    computed: {
        entries() {
            return this.$store.state.journal;
        },
    },
    methods: {
        iconType(type) {
            return type === 'error' ? 'warning' : 'info-alt'; 
        },
    },
}
</script>