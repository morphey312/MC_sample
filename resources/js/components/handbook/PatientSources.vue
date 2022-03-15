<template>
    <page
        :title="__('Источники информации')"
        type="flex">
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                :lazy="true"
                :label="__('Источники информации')"
                name="patient_sources" >
                <section class="shrinkable">
                    <sources-list
                        ref="table"
                        :filters="filters"
                        @selection-changed="setActiveItem"
                        @loaded="refreshed"
                        @header-filter-updated="syncFilters" >
                        <div class="buttons" slot="buttons">
                            <form-button
                                v-if="$canCreate('information-sources')"
                                :text="__('Добавить')"
                                icon="plus"
                                @click="create" />
                            <form-button
                                v-if="$canUpdate('information-sources')"
                                :disabled="activeItem === null || !$canManage('information-sources.update', activeItem.clinics)"
                                :text="__('Редактировать')"
                                icon="edit"
                                @click="edit" />
                            <form-button
                                v-if="$canDelete('information-sources')"
                                :disabled="activeItem === null || !$canManage('information-sources.delete', activeItem.clinics)"
                                :text="__('Удалить')"
                                icon="delete"
                                @click="remove" />
                             <form-button 
                                v-if="$can('action-logs.access')"
                                :disabled="activeItem === null"
                                :text="__('Операции')"
                                icon="menu-marketing"
                                @click="showLog" />
                        </div>
                    </sources-list>
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('media-types.access')"
                :lazy="true"
                :label="__('Виды рекламы')"
                name="media_types" >
                <section class="shrinkable">
                    <media-types />
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import SourcesList from './patient-sources/List.vue';
import FormCreate from './patient-sources/FormCreate.vue';
import FormEdit from './patient-sources/FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import MediaTypes from './MediaTypes';
import InformationSourceLog from '@/components/action-log/patient/InformationSource.vue';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        SourcesList,
        MediaTypes,
    },
    data() {
        let defaultTab = 'patient_sources';

        return {
            activeTab: defaultTab,
        }
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить источник информации'),
                editHeader: __('Редактировать источник информации'),
                width: '400px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот источник информации?'),
                deleted: __('Источник информации успешно удален'),
                created: __('Источник информации успешно добавлен'),
                updated: __('Источник информации успешно обновлен'),
            };
        },
        showLog() {
            this.$modalComponent(InformationSourceLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения источника информации'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
}
</script>
