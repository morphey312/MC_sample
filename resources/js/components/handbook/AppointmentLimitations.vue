<template>
	<page
        :title="__('Ограничения записи первичных пациентов')"
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
                <limitation-filter 
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters" 
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <limitation-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters"
                @edit-limitation="editLimitation" >
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$canCreate('limitations')"
                        :text="__('Добавить')"
                        icon="plus"
                        @click="create" />
                    <form-button 
                        v-if="$canUpdate('limitations')"
                        :disabled="activeItem === null || !$canManage('limitations.update', [activeItem.clinic_id])"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button 
                        v-if="$canDelete('limitations')"
                        :disabled="activeItem === null || !$canManage('limitations.delete', [activeItem.clinic_id])"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="removeLimitation" />
                </div>
            </limitation-list>
        </section>
    </page>
</template>

<script>
import LimitationList from './appointment-limitations/List.vue';
import LimitationFilter from './appointment-limitations/Filter.vue';
import FormCreate from './appointment-limitations/FormCreate.vue';
import FormEdit from './appointment-limitations/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        LimitationList,
        LimitationFilter,
    },
    methods: {
        getDefaultFilters() {
            let from = this.$moment().add(1, 'month').startOf('month').format('YYYY-MM-DD');
            let to = this.$moment().add(1, 'month').endOf('month').format('YYYY-MM-DD');

            return {
                dateFrom: from,
                dateTo: to,
                clinic: this.getLoggedUserClinics(),
            };
        },
        editLimitation(limitation) {
            this.getManageTable().updateSelection((item) => item.id == limitation.id);
            this.edit();
        },
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить ограничение'),
                editHeader: __('Изменить ограничение'),
                width: '770px',
                onClosed: () => {
                    if (this.needRefresh) {
                        this.refresh();
                    }
                },
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить это ограничение?'),
                deleted: __('Ограничение успешно удалено'),
                created: __('Ограничение успешно добавлено'),
                updated: __('Ограничение успешно обновлено'),
            };
        },
        removeLimitation() {
            if(this.hasLimitations()) {
                if (this.periodIsPastOrCurrent()) {
                    return this.$warning(__('Невозможно удалить. У врачей есть заданные параметры для этого периода'));
                }

                if (this.periodIsNext()) {
                    return this.remove(__('У врачей уже заданы параметры.'));
                }
            }
            return this.remove();
        },
        hasLimitations() {
            let hasParam = _.find(this.activeItem.doctors, (doctor) => { 
                return doctor.limitation_percent > 0;
            });
            return !_.isNil(hasParam);
        },
        periodIsPastOrCurrent() {
            let start = this.$moment(this.activeItem.date_from);
            let end = this.$moment(this.activeItem.date_to);
            let now = this.$moment();
            return now.isBetween(start, end, 'day') || end.isSameOrBefore(now, 'day') || start.isSame(now, 'day');
        },
        periodIsNext() {
            let start = this.$moment(this.activeItem.date_from);
            let now = this.$moment();
            return start.isAfter(now, 'day');
        },
        remove(subMessage = '') {
            let messages = {
                ...this.getMessages(),
            };
            let message = subMessage + messages.deleteConfirmation;
            let model = this.activeItem;
            let attributes = { ...this.activeItem._attributes };

            this.$confirm(message, () => {
                model.delete().then((response) => {
                    if (messages.deleted) {
                        this.$info(messages.deleted);
                    }
                    this.onDeleted(attributes);
                    this.lastActiveItemId = null;
                    this.activeItem = null;
                    this.refresh();
                });
            });
        },
    }
}
</script>