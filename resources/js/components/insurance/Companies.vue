<template>
    <page
        :title="__('Справочник страховых компаний')"
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
                <insurance-company-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <insurance-company-list
                :filters="filters"
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$canCreate('insurance')"
                        @click="create">
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$canUpdate('insurance')"
                        :disabled="activeItem === null || !$canManage('insurance.update', activeItemClinicsIds)"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$canDelete('insurance')"
                        :disabled="activeItem === null || !$canManage('insurance.delete', activeItemClinicsIds)"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </insurance-company-list>
        </section>
    </page>
</template>
<script>
import InsuranceCompanyList from './companies/List.vue';
import InsuranceCompanyFilter from './companies/Filter.vue';
import FormCreate from './companies/FormCreate.vue';
import FormEdit from './companies/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        InsuranceCompanyList,
        InsuranceCompanyFilter,
    },
    data() {
        return {
            needRefresh: false,
        };
    },
    computed: {
        activeItemClinicsIds() {
            return this.activeItem? this.activeItem.company_clinics.map(({clinic_id}) => clinic_id) : null;
        }
    },
    methods: {
        getDefaultFilters() {
            return {};
        },
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить страховую компанию'),
                editHeader: __('Изменить страховую компанию'),
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
                created: __('Страховая компания была успешно добавлена'),
                updated: __('Страховая компания была успешно обновлена'),
            };
        },
        refresh() {
            this.needRefresh = false;
            this.getManageTable().refresh();
        },
    },
}
</script>
