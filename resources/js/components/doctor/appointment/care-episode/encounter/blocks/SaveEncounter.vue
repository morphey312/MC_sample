<template>
    <div class="text-center">
        {{ __('У данного пациента нет записи в эпизоде.') }}
        {{ __('Чтобы продолжить, ') }}
        <a href="#" @click.prevent="createEncounter">{{ __('сохраните запись в эпизоде') }}</a>
    </div>
</template>

<script>
export default {
    methods: {
        createEncounter() {
            this.$emit('createEncounter');
        }
    }
}
</script>
