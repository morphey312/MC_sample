<template>
    <page
        :title="__('Группы доступа')"
        type="flex">
        <section class="grey-cap shrinkable">
            <roles-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @edit="editRole">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$can('roles.create')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button
                        v-if="$can('roles.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button
                        v-if="$can('roles.delete')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                    <form-button
                        v-if="$can('roles.access')"
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog" />
                </div>
            </roles-list>
        </section>
    </page>
</template>

<script>
import RolesList from './List.vue';
import CreateForm from './Create.vue';
import EditForm from './Edit.vue';
import ManageMixin from '@/mixins/manage';
import MoneyRecieverLog from "@/components/action-log/employee/Roles";

export default {
    mixins: [ManageMixin],
    components: {
        RolesList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: CreateForm,
                editForm: EditForm,
                createHeader: __('Добавить группу доступа'),
                editHeader: __('Изменить группу доступа'),
                width: '1000px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту группу доступа?'),
                deleted: __('Группа доступа была успешно удалена'),
                created: __('Группа доступа была успешно добавлена'),
                updated: __('Группа доступа была успешно обновлена'),
            };
        },
        editRole(role) {
            this.activeItem = role;
            this.edit();
        },
        showLog() {
            this.$modalComponent(MoneyRecieverLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения пучателя денег'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
}
</script>

