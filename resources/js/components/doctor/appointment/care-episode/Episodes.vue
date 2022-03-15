<template>
    <div class="episodes-container">
        <episodes-list
            ref="table"
            :episodes="episodes"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <el-button
                :disabled="activeItem === null"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="close">
                {{ __('Закрыть') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="cancel">
                {{ __('Удалить') }}
            </el-button>
        </div>
        <slot name="buttons"></slot>
    </div>
</template>

<script>
import ManageMixin from '@/mixins/manage';
import EpisodesList from './List.vue';
import EditForm from './EditForm.vue';
import CloseForm from './CloseForm.vue';
import CancelForm from './CancelForm.vue';

export default {
    components: {
        EpisodesList,
    },
    mixins: [
        ManageMixin,
    ],
    props: {
        episodes: Array,
    },
    methods: {
        getModalOptions() {
            return {
                editForm: EditForm,
                editHeader: __('Редактирование эпизода'),
                width: '500px',
            };
        },
        onUpdated(model) {
            this.$emit('updated', model);
        },
        onDeleted(attributes) {
            this.$emit('deleted', attributes.id);
        },
        close() {
            this.terminate(CloseForm, __('Закрытие эпизода'))
                .then((model) => {
                    if (model !== null) {
                        this.$emit('deleted', model.id);
                    }
                });
        },
        cancel() {
            this.terminate(CancelForm, __('Удаление эпизода'))
                .then((model) => {
                    if (model !== null) {
                        this.$emit('deleted', model.id);
                    }
                });
        },
        terminate(component, title) {
            return new Promise((resolve) => {
                this.$modalComponent(component, {
                    model: this.activeItem,
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                        resolve(null);
                    },
                    saved: (dialog, model) => {
                        dialog.close();
                        resolve(model);
                    },
                }, {
                    header: title,
                    width: '500px',
                });
            });
        },
    },
    watch: {
        episodes() {
            this.$nextTick(() => {
                this.refresh();
            });
        },
    }
}
</script>
