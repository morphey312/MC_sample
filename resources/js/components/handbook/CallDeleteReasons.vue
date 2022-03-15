<template>
    <page
        :title="__('Причины удаления звонка')"
        type="flex">
        <section class="grey-cap shrinkable">
            <call-delete-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$can('call-delete-reasons.create')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button 
                        v-if="$can('call-delete-reasons.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button 
                        v-if="$can('call-delete-reasons.delete')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                </div>
            </call-delete-list>
        </section>
    </page>
</template>

<script>
import CallDeleteList from './call-delete-reasons/List.vue';
import FormCreate from './call-delete-reasons/Create.vue';
import FormEdit from './call-delete-reasons/Edit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CallDeleteList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить причину'),
                editHeader: __('Изменить причину'),
                width: '500px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту причину?'),
                deleted: __('Причина успешно удалена'),
                created: __('Причина успешно добавлена'),
                updated: __('Причина успешно обновлена'),
            };
        },
    }
}
</script>