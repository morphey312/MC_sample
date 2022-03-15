<template>
    <page
        :title="__('Предоставители медицинских услуг')"
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
                <msp-filter 
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <msp-list 
                :filters="filters"
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('msp.create')"
                        @click="create">
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$can('msp.update')"
                        :disabled="activeItem === null"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$can('msp.delete')"
                        :disabled="activeItem === null"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </msp-list>
        </section>
    </page>
</template>
<script>
import MspList from './msp/List.vue';
import MspFilter from './msp/Filter.vue';
import FormCreate from './msp/FormCreate.vue';
import FormEdit from './msp/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        MspList,
        MspFilter,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить предоставителя медицинских услуг'),
                editHeader: __('Изменить предоставителя медицинских услуг'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                created: __('Предоставитель медицинских услуг был успешно добавлен'),
                updated: __('Предоставитель медицинских услуг был успешно обновлен'),
            };
        },
    },
}
</script>