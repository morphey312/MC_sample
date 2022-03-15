<template>
    <h2>{{ __('Выбранные очереди') }}: {{chosenQueues}}</h2>
</template>

<script>
export default {
    name: "CallsQueuesSubHeader",
    props: {
        chosenQueues: String,
    },
}
</script>
