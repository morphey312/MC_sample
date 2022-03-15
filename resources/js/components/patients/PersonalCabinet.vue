<template>
    <page
        :title="__('Регистрация ЛК')"
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
                <registrations-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <el-tabs v-model="activeTab" class="tab-group-grey shrinkable-tabs">
            <el-tab-pane
                v-if="$can('patient-registrations.access')"
                :lazy="true"
                :label="__('Заявки на регистрацию ЛК')"
                name="registrations" >
                <section class="pt-0 shrinkable">
                    <patient-registrations 
                        :initial-filters="registrationsFilters"
                        @header-filter-updated="syncFilters" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$can('patient-users.access')"
                :lazy="true"
                :label="__('Аккаунты пациентов в ЛК')"
                name="users" >
                <section class="pt-0 shrinkable">
                    <patient-users 
                        :initial-filters="usersFilters"
                        @header-filter-updated="syncFilters"/>
                </section>
            </el-tab-pane>
        </el-tabs>
    </page>
</template>

<script>
import PatientRegistrations from './Registrations.vue';
import PatientUsers from './Accounts.vue';
import RegistrationsFilter from './registrations/Filter.vue';
import FilterState from '@/mixins/filter-state';

export default {
    mixins: [
        FilterState,
    ],
    components: {
        RegistrationsFilter,
        PatientRegistrations,
        PatientUsers,
    },
    data() {
        let defaultFilters = this.getDefaultFilters();
        let filters = this.getStoredFilter(defaultFilters);
        return {
            filters,
            registrationsFilters: filters,
            usersFilters: filters,
            displayFilter: !_.isEqual(filters, defaultFilters),
            activeTab: 'registrations',
        };
    },
    methods: {
        changeFilters(filters) {
            this.storeFilter(filters);
            this.filters = filters;
            this.updateTabFilters();
        },
        clearFilters() {
            this.forgetFilter();
            this.filters = this.getDefaultFilters();
            this.updateTabFilters();
        },
        getDefaultFilters() {
            return {};
        },
        syncFilters(updates) {
            this.$refs.filter.sync(updates);
        },
        updateTabFilters() {
            if (this.activeTab === 'users') {
                this.usersFilters = this.filters;
            } else {
                this.registrationsFilters = this.filters;
            }
        }
    },
}
</script>