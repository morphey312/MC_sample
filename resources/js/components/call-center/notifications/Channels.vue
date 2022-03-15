<template>
    <channels-list
        ref="table"
        @selection-changed="setActiveItem"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
            <form-button 
                v-if="$can('notification-channels.create')"
                :text="__('Добавить')"
                icon="plus"
                @click="create" />
            <form-button 
                v-if="$can('notification-channels.update')"
                :disabled="activeItem === null"
                :text="__('Редактировать')"
                icon="edit"
                @click="edit" />
            <form-button 
                v-if="$can('notification-channels.delete')"
                :disabled="activeItem === null"
                :text="__('Удалить')"
                icon="delete"
                @click="remove" />
        </div>
    </channels-list>
</template>

<script>
import ChannelsList from './channels/List.vue';
import FormCreate from './channels/Create.vue';
import FormEdit from './channels/Edit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ChannelsList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить канал'),
                editHeader: __('Изменить канал'),
                width: '560px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот канал?'),
                deleted: __('Канал успешно удален'),
                created: __('Канал успешно добавлен'),
                updated: __('Канал успешно обновлен'),
            };
        },
    }
}
</script>