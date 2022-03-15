<template>
    <document-list
        ref="table"
        :filters="filters"
        @selection-changed="setActiveItem"
        @loaded="refreshed"
        @header-filter-updated="syncFilters">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$canCreate('patient-documents')"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$canUpdate('patient-documents')"
                :disabled="activeItem === null || !$canManage('patient-documents.update', activeItem.clinic_id)"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$canDelete('patient-documents')"
                :disabled="activeItem === null || !$canManage('patient-documents.delete', activeItem.clinic_id)"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </document-list>
</template>
<script>
import DocumentList from './ClinicList.vue';
import ManageMixin from '@/mixins/manage';
import DocumentFormMixin from './mixin/form';

export default {
    mixins: [
        ManageMixin,
        DocumentFormMixin,
    ],
    components: {
        DocumentList,
    },
    methods: {
        getDefaultFilters() {
            return {
                is_official_form: false,
            };
        },
    },
}
</script>