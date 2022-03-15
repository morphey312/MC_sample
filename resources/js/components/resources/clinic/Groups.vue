<template>
    <section class="grey-cap p-20 shrinkable">
        <groups-list
            ref="table"
            @selection-changed="setActiveItem"
            @loaded="refreshed">
            <div class="buttons" slot="buttons">
                <form-button
                    v-if="$can('clinic-groups.create')"
                    :text="__('Добавить')"
                    icon="plus"
                    @click="create" />
                <form-button
                    v-if="$can('clinic-groups.update')"
                    :disabled="activeItem === null"
                    :text="__('Редактировать')"
                    icon="edit"
                    @click="edit" />
                <form-button
                    v-if="$can('clinic-groups.delete')"
                    :disabled="activeItem === null"
                    :text="__('Удалить')"
                    icon="delete"
                    @click="remove" />
                <form-button
                    v-if="$can('clinic-groups.access')"
                    :disabled="activeItem === null"
                    :text="__('Операции')"
                    icon="menu-marketing"
                    @click="showLog" />
            </div>
        </groups-list>
    </section>
</template>

<script>
import GroupsList from './groups/List.vue';
import FormCreate from './groups/FormCreate.vue';
import FormEdit from './groups/FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import GroupLog from '@/components/action-log/clinic/clinic/Group.vue';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        GroupsList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить группу'),
                editHeader: __('Изменить группу'),
                width: '320px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту группу клиник?'),
                deleted: __('Группа клиник была успешно удалена'),
                created: __('Группа клиник была успешно добавлена'),
                updated: __('Группа клиник была успешно обновлена'),
            };
        },
        showLog() {
            this.$modalComponent(GroupLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения группы'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    }
}
</script>

