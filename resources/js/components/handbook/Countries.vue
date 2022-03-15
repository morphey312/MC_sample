<template>
    <page
        :title="__('Страны')"
        type="flex">
        <section class="shrinkable">
            <countries-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('countries.create')"
                        @click="create" >
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$can('countries.update')"
                        :disabled="activeItem === null"
                        @click="edit" >
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$can('countries.delete')"
                        :disabled="activeItem === null"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </countries-list>
        </section>
    </page>
</template>

<script>
import CountriesList from './countries/List.vue';
import FormCreate from './countries/FormCreate.vue';
import FormEdit from './countries/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CountriesList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить страну'),
                editHeader: __('Редактировать страну'),
                width: '400px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту страну?'),
                deleted: __('Страна успешно удалена'),
                created: __('Страна успешно добавлена'),
                updated: __('Страна успешно обновлена'),
            };
        }
    }
}
</script>
