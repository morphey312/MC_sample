<template>
    <handbook-list
        ref="table"
        category="media_type"
        :header="__('Название')"
        @selection-changed="setActiveItem"
        @loaded="refreshed">
        <div class="buttons" slot="buttons">
            <form-button
                v-if="$can('media-types.create')"
                :text="__('Добавить')"
                icon="plus"
                @click="create" />
            <form-button
                v-if="$can('media-types.update')"
                :disabled="activeItem === null"
                :text="__('Редактировать')"
                icon="edit"
                @click="edit" />
            <form-button
                v-if="$can('media-types.delete')"
                :disabled="activeItem === null"
                :text="__('Удалить')"
                icon="delete"
                @click="remove" />
        </div>
    </handbook-list>
</template>

<script>
import HandbookList from '@/components/handbook/handbook/List.vue';
import HandbookForm from '@/components/handbook/handbook/Form.vue';
import HandbookModel from '@/models/handbook';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        HandbookList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: HandbookForm,
                createProps: () => ({
                    model: new HandbookModel({
                        category: 'media_type',
                    }),
                }),
                editForm: HandbookForm,
                editProps: () => ({
                    model: this.activeItem.clone(),
                }),
                createHeader: __('Добавить запись'),
                editHeader: __('Изменить запись'),
                width: '350px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту запись?'),
                deleted: __('Запись была успешно удалена'),
                created: __('Запись была успешно добавлена'),
                updated: __('Запись была успешно обновлена'),
            };
        },
    },
}
</script>

