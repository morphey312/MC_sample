<template>
    <analysis-templates-list
        ref="table"
        :filters="filters"
        @selection-changed="setActiveItem"
        @header-filter-updated="syncFilters">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$canCreate('analysis-templates')"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$canUpdate('analysis-templates')"
                :disabled="activeItem === null || !$canManage('analysis-templates.update', activeItem.clinics)"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$canDelete('analysis-templates')"
                :disabled="activeItem === null || !$canManage('analysis-templates.delete', activeItem.clinics)"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </analysis-templates-list>
</template>

<script>
import AnalysisTemplatesList from './analysis-templates/List.vue';
import CreateAnalysisTemplate from './analysis-templates/FormCreate.vue';
import EditAnalysisTemplate from './analysis-templates/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [ManageMixin],
    components: {
        AnalysisTemplatesList,
    },
    methods: {
        getDefaultFilters() {
            return {};
        },
        getModalOptions() {
            return {
                createForm: CreateAnalysisTemplate,
                editForm: EditAnalysisTemplate,
                createHeader: __('Добавить шаблон результата анализов'),
                editHeader: __('Изменить шаблон результата анализов'),
                width: '900px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот шаблон результата анализов?'),
                deleted: __('Шаблон результата анализов был успешно удален'),
                created: __('Шаблон результата анализов был успешно добавлен'),
                updated: __('Шаблон результата анализов был успешно обновлен'),
            };
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
};
</script>
