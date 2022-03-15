<template>
    <protocol-templates-list
        ref="table"
        :filters="filters"
        @selection-changed="setActiveItem"
        @header-filter-updated="syncFilters">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$canCreate('protocol-templates')"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$canUpdate('protocol-templates')"
                :disabled="activeItem === null || !$canManage('protocol-templates.update', activeItem.clinics)"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$canDelete('protocol-templates')"
                :disabled="activeItem === null || !$canManage('protocol-templates.delete', activeItem.clinics)"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </protocol-templates-list>
</template>

<script>
import ProtocolTemplatesList from './protocol-templates/List.vue';
import CreateProtocolTemplate from './protocol-templates/FormCreate.vue';
import EditProtocolTemplate from './protocol-templates/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [ManageMixin],
    components: {
        ProtocolTemplatesList,
    },
    methods: {
        getDefaultFilters() {
            return {};
        },
        getModalOptions() {
            return {
                createForm: CreateProtocolTemplate,
                editForm: EditProtocolTemplate,
                createHeader: __('Добавить протокол исследования'),
                editHeader: __('Изменить протокол исследования'),
                width: '700px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот протокол исследования?'),
                deleted: __('Протокол исследования был успешно удален'),
                created: __('Протокол исследования был успешно добавлен'),
                updated: __('Протокол исследования был успешно обновлен'),
            };
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
};
</script>
