<template>
    <page
        :title="__('Иконки')"
        type="flex">
        <section class="grey-cap shrinkable">
            <icon-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('discount-card-icons.create')"
                        @click="create">
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$can('discount-card-icons.update')"
                        :disabled="activeItem === null"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$can('discount-card-icons.delete')"
                        :disabled="activeItem === null"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </icon-list>
        </section>
    </page>
</template>

<script>
import IconList from './icons/List.vue';
import CreateIcon from './icons/FormCreate.vue';
import EditIcon from './icons/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        IconList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: CreateIcon,
                editForm: EditIcon,
                createHeader: __('Добавить иконку'),
                editHeader: __('Изменить иконку'),
                width: '500px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту иконку?'),
                deleted: __('Иконка была успешно удалена'),
                created: __('Иконка была успешно добавлена'),
                updated: __('Иконка была успешно обновлена'),
            };
        },
    },
};
</script>