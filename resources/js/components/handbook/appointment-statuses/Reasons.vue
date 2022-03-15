<template>
    <reason-list
        ref="table"
        @selection-changed="setActiveItem"
        @loaded="refreshed" >
        <div class="buttons" slot="buttons">
            <form-button 
                v-if="$can('appointment-status-reasons.create')"
                :text="__('Добавить')"
                icon="plus"
                @click="create" />
            <form-button 
                v-if="$can('appointment-status-reasons.update')"
                :disabled="activeItem === null"
                :text="__('Редактировать')"
                icon="edit"
                @click="edit" />
            <form-button 
                v-if="$can('appointment-status-reasons.delete')"
                :disabled="activeItem === null"
                :text="__('Удалить')"
                icon="delete"
                @click="remove" />
        </div>
    </reason-list>
</template>

<script>
import ReasonList from './reasons/List.vue';
import FormCreate from './reasons/FormCreate.vue';
import FormEdit from './reasons/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    components: {
        ReasonList,
    },
    mixins: [
        ManageMixin,
    ],
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавление причины смены статуса'),
                editHeader: __('Редактирование причины смены статуса'),
                width: '390px',
                onClosed: () => {
                    if (this.needRefresh) {
                        this.refresh();
                    }
                },
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этоту причину?'),
                deleted: __('Причина успешно удалена'),
                created: __('Причина успешно добавлена'),
                updated: __('Причина успешно обновлена'),
            };
        },
    }
}
</script>