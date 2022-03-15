<template>
    <clinic-route-list
        ref="table"
        :filters="filters"
        @selection-changed="setActiveItem"
        @loaded="refreshed"
        @header-filter-updated="syncFilters">
        <div class="buttons" slot="buttons">
            <el-button
                v-if="$canCreate('patient-clinic-routes')"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$canUpdate('patient-clinic-routes')"
                :disabled="activeItem === null || !$canManage('patient-clinic-routes.update', activeItem.clinic_id)"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$canDelete('patient-clinic-routes')"
                :disabled="activeItem === null || !$canManage('patient-clinic-routes.delete', activeItem.clinic_id)"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </clinic-route-list>
</template>
<script>
import ClinicRouteList from './List.vue';
import CreateForm from './FormCreate.vue';
import EditForm from './FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ClinicRouteList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: CreateForm,
                editForm: EditForm,
                createHeader: __('Добавить маршрут пациента'),
                editHeader: __('Изменить маршрут пациента'),
                width: '700px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот маршрут пациента?'),
                deleted: __('Маршрут пациента был успешно удален'),
                created: __('Маршрут пациента был успешно добавлен'),
                updated: __('Маршрут пациента был успешно обновлен'),
            };
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
}
</script>