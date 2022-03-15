<template>
    <div>
        <template v-if="wasSubmitted">
            <alert 
                v-if="fullyPaid && hasEmail && autoEmail"
                type="attention"
                class="mb-10">
                {{ __('Результат анализа уже был отправлен пациенту. После замены результата он будет отправлен заново.') }}
            </alert>
            <alert 
                v-else
                type="attention"
                class="mb-10">
                {{ __('Внимание! Результат анализа уже был отправлен пациенту.') }}
            </alert>
        </template>
        <template>
            <alert v-if="!fullyPaid" 
                type="attention"
                class="mb-10">
                {{ __('Анализ не оплачен или оплачен не полностью, поэтому результат не будет отправлен пациенту автоматически.') }}
            </alert>
            <alert 
                v-if="!hasEmail" 
                type="attention"
                class="mb-10">
                {{ __('У данного пациента не указан email, поэтому результат не будет отправлен пациенту автоматически.') }}
            </alert>
            <alert 
                v-else-if="!autoEmail" 
                type="attention"
                class="mb-10">
                {{ __('Для данного пациента отключена функция автоматической отправки.') }}
            </alert>
        </template>
    </div>
</template>

<script>
export default {
    props: {
        wasSubmitted: Boolean,
        fullyPaid: Boolean,
        hasEmail: Boolean,
        autoEmail: Boolean,
    },
};
</script>