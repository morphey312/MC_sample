<template>
    <page
        :title="__('Справочник корпоративных клиентов')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey filter">
                <legal-entity-filter 
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <legal-entity-list 
                :filters="filters"
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$can('legal-entities.create')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button 
                        v-if="$can('legal-entities.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button 
                        v-if="$can('legal-entities.delete')"
                        :disabled="activeItem === null"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                </div>
            </legal-entity-list>
        </section>
    </page>
</template>
<script>
import LegalEntityList from './entities/List.vue';
import LegalEntityFilter from './entities/Filter.vue';
import FormCreate from './entities/FormCreate.vue';
import FormEdit from './entities/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        LegalEntityList,
        LegalEntityFilter,
    },
    data() {
        return {
            needRefresh: false,
        };
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить корпоративного клиента'),
                editHeader: __('Изменить корпоративного клиента'),
                width: '770px',
                onClosed: () => {
                    if (this.needRefresh) {
                        this.refresh();
                    }
                },
                events: {
                    clinicsUpdated: () => {
                        this.needRefresh = true;
                    },
                },
            };
        },
        getMessages() {
            return {
                created: __('Корпоративный клиент был успешно добавлен'),
                updated: __('Корпоративный клиент был успешно обновлен'),
            };
        },
        refresh() {
            this.needRefresh = false;
            this.getManageTable().refresh();
        },
    },
}
</script>