<template>
    <status-list
        ref="table"
        @selection-changed="setActiveItem"
        @loaded="refreshed" >
        <div class="buttons" slot="buttons">
            <form-button 
                v-if="$can('appointment-statuses.create')"
                :text="__('Добавить')"
                icon="plus"
                @click="create" />
            <form-button 
                v-if="$can('appointment-statuses.update')"
                :disabled="activeItem === null"
                :text="__('Редактировать')"
                icon="edit"
                @click="edit" />
            <form-button 
                v-if="$can('appointment-statuses.delete')"
                :disabled="activeItem === null"
                :text="__('Удалить')"
                icon="delete"
                @click="remove" />
        </div>
    </status-list>
</template>

<script>
import StatusList from './List.vue';
import FormCreate from './FormCreate.vue';
import FormEdit from './FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        StatusList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавление статуса'),
                editHeader: __('Редактирование статуса'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот статус?'),
                deleted: __('Статус успешно удален'),
                created: __('Статус успешно добавлен'),
                updated: __('Статус успешно обновлен'),
            };
        },
    }
}
</script>