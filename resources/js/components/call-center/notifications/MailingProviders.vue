<template>
    <mailing-providers-list
        ref="table"
        @selection-changed="setActiveItem"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
            <form-button
                v-if="$can('notification-mailing-providers.create')"
                :text="__('Добавить')"
                icon="plus"
                @click="create" />
            <form-button
                v-if="$can('notification-mailing-providers.update')"
                :disabled="activeItem === null"
                :text="__('Редактировать')"
                icon="edit"
                @click="edit" />
            <form-button
                v-if="$can('notification-mailing-providers.delete')"
                :disabled="activeItem === null"
                :text="__('Удалить')"
                icon="delete"
                @click="remove" />
        </div>
    </mailing-providers-list>
</template>

<script>
import MailingProvidersList from './mailing-providers/List.vue';
import FormCreate from './mailing-providers/Create.vue';
import FormEdit from './mailing-providers/Edit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        MailingProvidersList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить шаблон'),
                editHeader: __('Изменить шаблон'),
                width: '640px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот шаблон?'),
                deleted: __('Шаблон успешно удален'),
                created: __('Шаблон успешно добавлен'),
                updated: __('Шаблон успешно обновлен'),
            };
        },
    }
}
</script>
