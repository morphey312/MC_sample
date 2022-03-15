<template>
    <transfer-list
        ref="table"
        @show-details="showDetails"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
        </div>
    </transfer-list>
</template>
<script>
import TransferList from './TransferList.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        TransferList,
    },
     methods: {
        showDetails(item) {
            this.$emit('show-details', item, true);
        },
    }
}
</script>
