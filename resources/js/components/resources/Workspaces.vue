<template>
    <page
        :title="__('Кабинеты')"
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
                <workspace-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <workspace-list
                :filters="filters"
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$canCreate('workspaces')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button
                        v-if="$canUpdate('workspaces')"
                        :disabled="activeItem === null || !$canManage('workspaces.update', activeItemClinicsIds)"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button
                        v-if="$canDelete('workspaces')"
                        :disabled="activeItem === null || !$canManage('workspaces.delete', activeItemClinicsIds)"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                    <form-button
                        v-if="$canAccess('workspaces')"
                        :disabled="activeItem === null || !$canManage('workspaces.access', activeItemClinicsIds)"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog" />
                </div>
            </workspace-list>
        </section>
    </page>
</template>

<script>
import WorkspaceList from './workspace/List.vue';
import WorkspaceFilter from './workspace/Filter.vue';
import FormCreate from './workspace/FormCreate.vue';
import FormEdit from './workspace/FormEdit.vue';
import WorkspaceLog from '@/components/action-log/Workspace.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        WorkspaceList,
        WorkspaceFilter,
    },
    data() {
        return {
            displayFilter: true,
            needRefresh: false,
        };
    },
    computed: {
        activeItemClinicsIds() {
            return this.activeItem.workspace_clinics.map(({clinic_id}) => clinic_id);
        }
    },
    methods: {
        showLog() {
            this.$modalComponent(WorkspaceLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения кабинета'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        getDefaultFilters() {
            return {
                clinic: this.getLoggedUserClinics(),
                is_active: 1
            };
        },
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавление кабинета'),
                editHeader: __('Изменить кабинет'),
                width: '850px',
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
                created: __('Кабинет был успешно добавлен'),
                updated: __('Кабинет был успешно обновлен'),
            };
        },
        refresh() {
            this.needRefresh = false;
            this.getManageTable().refresh();
        },
    },
}
</script>
