<template>
    <page
        :title="__('Результаты звонков')"
        type="flex">
        <section class="shrinkable">
            <call-result-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed" >
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$can('call-results.create')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button 
                        v-if="$can('call-results.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button 
                        v-if="$can('call-results.delete')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                </div>
            </call-result-list>
        </section>
    </page>
</template>

<script>
import CallResultList from './call-results/List.vue';
import FormCreate from './call-results/FormCreate.vue';
import FormEdit from './call-results/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CallResultList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить результат звонка'),
                editHeader: __('Изменить результат звонка'),
                width: '455px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот результат звонка?'),
                deleted: __('Результат звонка успешно удален'),
                created: __('Результат звонка успешно добавлен'),
                updated: __('Результат звонка успешно обновлен'),
            };
        },
    }
}
</script>