<template>
    <page
        :title="__('Должности')"
        type="flex">
        <section class="grey-cap shrinkable">
            <position-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$canCreate('positions')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button
                        v-if="$canUpdate('positions')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button
                        v-if="$canDelete('positions')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                </div>
            </position-list>
        </section>
    </page>
</template>

<script>
import PositionList from './List.vue';
import CreatePosition from './Create.vue';
import EditPosition from './Edit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [ManageMixin],
    components: {
        PositionList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: CreatePosition,
                editForm: EditPosition,
                createHeader: __('Добавить должность'),
                editHeader: __('Изменить должность'),
                width: '350px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту должность?'),
                deleted: __('Должность была успешно удалена'),
                created: __('Должность была успешно добавлена'),
                updated: __('Должность была успешно обновлена'),
            };
        },
    },
}
</script>

