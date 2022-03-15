<template>
    <section class="p-0 shrinkable-tabs">
        <div class="content-wrapper">
            <section class="grey filter">
                <clinic-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
            <section class="grey-cap p-20 shrinkable">
                <clinic-list
                    ref="table"
                    :filters="filters"
                    @selection-changed="setActiveItem"
                    @loaded="refreshed"
                    @header-filter-updated="syncFilters">
                    <div class="buttons" slot="buttons">
                        <form-button
                            v-if="$can('clinics.create')"
                            :text="__('Добавить')"
                            icon="plus"
                            @click="create" />
                        <form-button
                            v-if="$can('clinics.update')"
                            :disabled="activeItem === null"
                            :text="__('Редактировать')"
                            icon="edit"
                            @click="edit" />
                        <form-button
                            v-if="$can('clinics.delete')"
                            :disabled="activeItem === null"
                            :text="__('Удалить')"
                            icon="delete"
                            @click="remove" />
                        <form-button
                            v-if="$can('clinics.access')"
                            :disabled="activeItem === null"
                            :text="__('Операции')"
                            icon="menu-marketing"
                            @click="showLog" />
                    </div>
                </clinic-list>
            </section>
        </div>
    </section>
</template>

<script>
import ClinicList from './List.vue';
import ClinicFilter from './Filter.vue';
import FormCreate from './FormCreate.vue';
import FormEdit from './FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import ClinicLog from '@/components/action-log/clinic/Clinic.vue';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ClinicList,
        ClinicFilter,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить клинику'),
                editHeader: __('Изменить клинику'),
                width: '800px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту клинику?'),
                deleted: __('Клиника была успешно удалена'),
                created: __('Клиника была успешно добавлена'),
                updated: __('Клиника была успешно обновлена'),
            };
        },
        getDefaultFilters() {
            return {
                status: 1,
            };
        },
        showLog() {
            this.$modalComponent(ClinicLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения клиники'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    }
}
</script>

