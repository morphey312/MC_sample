<template>
    <page
        :title="title"
        type="flex">
        <section class="grey-cap shrinkable">
            <handbook-list
                ref="table"
                :category="category"
                :model-key="modelKey"
                :header="__('Название')"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$can(permissionGroup + '.create')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button
                        v-if="$can(permissionGroup + '.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button
                        v-if="$can(permissionGroup + '.delete')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                </div>
            </handbook-list>
        </section>
    </page>
</template>

<script>
import HandbookList from './List.vue';
import handbook from '@/services/handbook';
import HandbookForm from './Form.vue';
import HandbookModel from '@/models/handbook';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        HandbookList,
    },
    props: {
        title: String,
        category: String,
        permissionGroup: String,
        modelKey: Boolean
    },
    methods: {
        getModalOptions() {
            return {
                createForm: HandbookForm,
                createProps: () => ({
                    model: new HandbookModel({
                        category: this.category,
                    }),
                    modelKey: this.modelKey,
                }),
                editForm: HandbookForm,
                editProps: () => ({
                    model: this.activeItem.clone(),
                    modelKey: this.modelKey,
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
