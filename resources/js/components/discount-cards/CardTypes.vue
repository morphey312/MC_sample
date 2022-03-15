<template>
    <page
        :title="__('Типы дисконтных карт')"
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
            <section class="grey">
                <card-type-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters"  />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <card-type-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters"
                @syncFilters="syncFilters"
                @edit-card-type="editCardType">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$canCreate('discount-card-types')"
                        @click="create">
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$canUpdate('discount-card-types')"
                        :disabled="activeItem === null || !$canManage('discount-card-types.update', activeItemClinicsIds)"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$canDelete('discount-card-types')"
                        :disabled="activeItem === null || !$canManage('discount-card-types.delete', activeItemClinicsIds)"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </card-type-list>
        </section>
    </page>
</template>
<script>
import CardTypeList from './card-types/List.vue';
import CardTypeFilter from './card-types/Filter.vue';
import CreateCardType from './card-types/FormCreate.vue';
import EditCardType from './card-types/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin
    ],
    components: {
        CardTypeList,
        CardTypeFilter
    },
    data(){
        return {
            displayFilter: true,
        }
    },
    computed: {
        activeItemClinicsIds() {
            return this.activeItem.clinics.map(({clinic_id}) => clinic_id);
        }
    },
    methods: {
        getModalOptions() {
            return {
                createForm: CreateCardType,
                editForm: EditCardType,
                createHeader: __('Добавить тип дисконтных карт'),
                editHeader: __('Изменить тип дисконтных карт'),
                width: '900px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот тип дисконтных карт?'),
                deleted: __('Тип дисконтных карт был успешно удален'),
                created: __('Тип дисконтных карт был успешно добавлен'),
                updated: __('Тип дисконтных карт был успешно обновлен'),
            };
        },
        editCardType(cardType) {
            this.getManageTable().updateSelection((item) => item.id == cardType.id);
            this.edit();
        },
    },
};
</script>
