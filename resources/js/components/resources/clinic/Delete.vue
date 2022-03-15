<template>
    <modal
        :header="__('Удалить клинику?')"
        :visible="visible"
        width="500px"
        @close="toggleShow">
        <el-row>
            <el-col :span="12" class="text-center">
                <el-button @click="toggleShow">
                    {{ __('Отменить') }}
                </el-button>
            </el-col>
            <el-col :span="12" class="text-center">
                <el-button
                    type="primary"
                    @click.prevent="deleteReason">
                    {{ __('Удалить') }}
                </el-button>
            </el-col>
        </el-row>
    </modal>
</template>

<script>
import Modal from '@/components/general/Modal.vue';

export default {
    components: {
        Modal,
    },
    props: {
        activeItem: Object,
        visible: Boolean,
    },
    data() {
        return {
            model: this.activeItem,
        }
    },
    methods: {
        toggleShow(updated = false) {
            this.$emit('clinic-deleted', {updated: updated});
        },
        deleteReason() {
            this.model.delete().then((response) => {
                this.$info(__('Клиника успешно удалена'));
                this.toggleShow(true);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        }
    }
}
</script>