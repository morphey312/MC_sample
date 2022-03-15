<template>
    <page
        :title="__('Причины удаления записи пациента')"
        type="flex">
        <section class="shrinkable">
            <delete-reason-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed" >
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$can('appointment-delete-reasons.create')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button 
                        v-if="$can('appointment-delete-reasons.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button 
                        v-if="$can('appointment-delete-reasons.delete')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                </div>
            </delete-reason-list>
        </section>
    </page>
</template>

<script>
import DeleteReasonList from './appointment-delete-reasons/List.vue';
import FormCreate from './appointment-delete-reasons/FormCreate.vue';
import FormEdit from './appointment-delete-reasons/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        DeleteReasonList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить причину удаления записи пациента'),
                editHeader: __('Редактировать причину удаления записи пациента'),
                width: '450px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту причину?'),
                deleted: __('Причина успешно удалена'),
                created: __('Причина успешно добавлена'),
                updated: __('Причина успешно обновлена'),
            };
        }
    }
}
</script>