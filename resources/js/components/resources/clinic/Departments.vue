<template>
    <div>
        <section class="grey-cap p-20">
            <departments-list 
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$can('departments.create')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button 
                        v-if="$can('departments.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button 
                        v-if="$can('departments.delete')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                </div>
            </departments-list>
        </section>
    </div>
</template>

<script>
import DepartmentsList from './departments/List.vue';
import FormCreate from './departments/FormCreate.vue';
import FormEdit from './departments/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        DepartmentsList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить отделение'),
                editHeader: __('Изменить отделение'),
                width: '600px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить это отделение?'),
                deleted: __('Отделение успешно удалено'),
                created: __('Отделение успешно добавлено'),
                updated: __('Отделение успешно обновлено'),
            };
        },
    }
}
</script>

