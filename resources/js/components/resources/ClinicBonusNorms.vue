<template>
    <page
        :title="__('Бонусы операторов (нормы клиник)')"
        type="flex">
        <section class="shrinkable grey-cap">
            <norm-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$can('operator-bonuses.create')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button 
                        v-if="$can('operator-bonuses.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button 
                        v-if="$can('operator-bonuses.delete')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                </div>
            </norm-list>
        </section>
    </page>
</template>

<script>
import NormList from './operator-bonuses/List.vue';
import FormCreate from './operator-bonuses/FormCreate.vue';
import FormEdit from './operator-bonuses/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        NormList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить нормы клиники'),
                editHeader: __('Редактировать нормы клиники'),
                width: '800px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту норму?'),
                deleted: __('Норма успешно удалена'),
                created: __('Норма успешно добавлена'),
                updated: __('Норма успешно обновлена'),
            };
        }
    }
}
</script>