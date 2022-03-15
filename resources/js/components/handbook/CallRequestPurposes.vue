<template>
    <page
        :title="__('Цели прозвона')"
        type="flex">
        <section class="shrinkable">
            <call-purpose-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed" >
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$can('call-request-purposes.create')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button 
                        v-if="$can('call-request-purposes.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button 
                        v-if="$can('call-request-purposes.delete')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                </div>
            </call-purpose-list>
        </section>
    </page>
</template>

<script>
import CallPurposeList from './call-request-purposes/List.vue';
import FormCreate from './call-request-purposes/FormCreate.vue';
import FormEdit from './call-request-purposes/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CallPurposeList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить цель прозвона'),
                editHeader: __('Изменить цель прозвона'),
                width: '790px',
                onClosed: () => {
                    if (this.needRefresh) {
                        this.refresh();
                    }
                },
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этоту цель прозвона?'),
                deleted: __('Цель прозвона успешно удалена'),
                created: __('Цель прозвона успешно добавлена'),
                updated: __('Цель прозвона успешно обновлена'),
            };
        }
    }
}
</script>