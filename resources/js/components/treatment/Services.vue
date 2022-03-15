<template>
    <page
        :title="__('Услуги')"
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
                <service-filter
                    ref="filter"
                    :initial-state="filters"
                    permissions="services"
                    @changed="changeFiltersAndShowTable"
                    @cleared="clearFiltersAndHideTable" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <services-list
                v-if="displayTable"
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$canCreate('services')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button
                        v-if="$canUpdate('services') || $can('services.update-pc-only')"
                        :disabled="$can('services.update-pc-only') ? activeItem === null : activeItem === null || !$canManage('services.update', activeItem.clinic_ids)"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button
                        v-if="$canAccess('service-prices')"
                        :disabled="activeItem === null"
                        :text="__('Тарифы')"
                        icon="dollar-alt"
                        @click="showPrices" />
                    <form-button
                        v-if="$canDelete('services')"
                        :disabled="activeItem === null || !$canManage('services.delete', activeItem.clinic_ids)"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="remove" />
                    <form-button
                        v-if="$can('action-logs.access') || $can('services.update-pc-only')"
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog" />
                </div>
            </services-list>
        </section>
    </page>
</template>

<script>
import ServicesList from './services/List.vue';
import CreateService from './services/FormCreate.vue';
import EditService from './services/FormEdit.vue';
import ServiceFilter from './services/Filter.vue';
import ManageMixin from '@/mixins/manage';
import ServiceLog from '@/components/action-log/Service.vue';

export default {
    name: 'Services',
    mixins: [
        ManageMixin
    ],
    components: {
        ServiceFilter,
        ServicesList,
    },
    data(){
        return {
            displayFilter: true,
            displayTable: false
        }
    },
    methods: {
        showPrices() {
            this.$router.push({name: 'services-prices', params: {id: this.activeItem.id}});
        },
        getDefaultFilters() {
            let today = this.$moment().format('YYYY-MM-DD');
            return {
                disabled: 0,
                has_price: {
                    from: today,
                    to: today,
                },
            };
        },
        getModalOptions() {
            return {
                createForm: CreateService,
                editForm: EditService,
                createHeader: __('Добавить услугу'),
                editHeader: __('Изменить услугу'),
                width: '700px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту услугу?'),
                deleted: __('Услуга была успешно удалена'),
                created: __('Услуга была успешно добавлена'),
                updated: __('Услуга была успешно обновлена'),
            };
        },
        showLog() {
            this.$modalComponent(ServiceLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения услуги'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        clearFiltersAndHideTable() {
            this.displayTable = false;
            this.clearFilters();
        },
        changeFiltersAndShowTable(filters) {
            this.changeFilters(filters);
            this.displayTable = true;
        },
    },
};
</script>
