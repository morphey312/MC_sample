<template>
    <div class="unavailable-container">
        <unavailable-list 
            ref="table"
            :model="model"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <el-button
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import UnavailableList from './List.vue';
import Form from './Form.vue';
import ManageMixin from '@/mixins/manage';
import ServiceUnavailable from '@/models/clinic/service-unavailable';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        UnavailableList,
    },
    props: {
        model: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: Form,
                createProps: {
                    model: new ServiceUnavailable(),
                    isCreate: true,
                },
                editForm: Form,
                editProps: () => ({
                    model:  this.activeItem.clone(),
                }),
                createHeader: __('Добавить период недоступности'),
                editHeader: __('Обновить период недоступности'),
                width: '400px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот период недоступности?'),
                deleted: __('Период недоступности был успешно удален'),
                created: __('Период недоступности был успешно создан'),
                updated: __('Период недоступности был успешно обновлен'),
            };
        },
        onCreated(model) {
            this.model.not_available.push(model);
        },
        onUpdated(model) {
            if (this.activeItem) {
                this.activeItem.assign(model.attributes);
            }
        },
        performDelete(model) {
            this.model.not_available = this.model.not_available.filter((m) => m !== model);
            return Promise.resolve();
        },
    },
}
</script>