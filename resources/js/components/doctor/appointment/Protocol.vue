<template>
    <div class="protocols-block"
        v-if="protocols.length !== 0">
        <h3>{{ __('Протоколы исследований') }}</h3>
        <protocols-table
            :protocols="protocols" />
    </div>
</template>

<script>
import ProtocolsTable from './protocol/Table.vue';

export default {
    components: {
        ProtocolsTable,
    },
    props: {
        protocols: Array,
    },
}   
</script>