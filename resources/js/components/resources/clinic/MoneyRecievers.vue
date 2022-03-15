<template>
    <section class="grey-cap p-20 shrinkable">
        <recievers-list
            ref="table"
            @selection-changed="setActiveItem"
            @loaded="refreshed">
            <div class="buttons" slot="buttons">
                <form-button
                    v-if="$canCreate('money-recievers')"
                    :text="__('Добавить')"
                    icon="plus"
                    @click="create" />
                <form-button
                    v-if="$canUpdate('money-recievers')"
                    :disabled="activeItem === null"
                    :text="__('Редактировать')"
                    icon="edit"
                    @click="edit" />
                <form-button
                    v-if="$canDelete('money-recievers')"
                    :disabled="activeItem === null"
                    :text="__('Удалить')"
                    icon="delete"
                    @click="remove" />
                <form-button
                    v-if="$canAccess('money-recievers')"
                    :disabled="activeItem === null"
                    :text="__('Операции')"
                    icon="menu-marketing"
                    @click="showLog" />
            </div>
        </recievers-list>
    </section>
</template>

<script>
import RecieversList from './money-recievers/List.vue';
import FormCreate from './money-recievers/FormCreate.vue';
import FormEdit from './money-recievers/FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import MoneyRecieverLog from '@/components/action-log/clinic/clinic/MoneyReciever';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        RecieversList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить получателя денег'),
                editHeader: __('Изменить получателя денег'),
                width: '720px',
                beforeClose: (dialog) => {
                    let form = dialog.getTopComponent();
                    return form.checkPreventClose === undefined
                        || form.checkPreventClose() !== false;
                },
                onClosed: () => {
                    if (this.needRefresh) {
                        this.refresh();
                    }
                },
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этого получателя денег?'),
                deleted: __('Получатель денег успешно удален'),
                created: __('Получатель денег был успешно добавлен'),
                updated: __('Получатель денег был успешно обновлен'),
            };
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
    }
}
</script>

