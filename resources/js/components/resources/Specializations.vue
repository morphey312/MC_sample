<template>
    <page
        :title="__('Специализации')"
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
                <specialization-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <specialization-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters" >
                <div class="buttons" slot="buttons">
                    <form-button
                        v-if="$canCreate('specializations')"
                        :text="__('Добавить специализацию')"
                        icon="plus"
                        @click="create" />
                    <form-button
                        v-if="$canUpdate('specializations')"
                        :disabled="activeItem === null || !$canManage('specializations.update', clinicsIdsOfActiveItem)"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                    <form-button
                        v-if="$canDelete('specializations')"
                        :disabled="activeItem === null || !$canManage('specializations.delete', clinicsIdsOfActiveItem)"
                        :text="__('Удалить')"
                        icon="delete"
                        @click="removeSpecialization" />
                    <form-button
                        v-if="$canAccess('specializations')"
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        icon="menu-marketing"
                        @click="showLog" />
                </div>
            </specialization-list>
        </section>
    </page>
</template>

<script>
import SpecializationList from './specializations/List.vue';
import SpecializationFilter from './specializations/Filter.vue';
import SpecializationLog from '@/components/action-log/clinic/Specialization.vue';
import ClinicRepository from '@/repositories/clinic';
import Specialization from '@/models/specialization';
import FormCreate from './specializations/FormCreate.vue';
import FormEdit from './specializations/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        SpecializationList,
        SpecializationFilter,
    },
    data() {
        return {
            needRefresh: false,
            displayFilter: true,
        }
    },
    computed: {
        clinicsIdsOfActiveItem() {
            return this.activeItem.clinics.map(({clinic_id}) => clinic_id);
        }
    },
    methods: {
        showLog() {
            this.$modalComponent(SpecializationLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения специализации'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
        getDefaultFilters() {
            return {
                clinic: this.getLoggedUserClinics(),
            };
        },
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавление специализации'),
                editHeader: __('Редактирование специализации'),
                width: '770px',
                beforeClose: (dialog) => {
                    let form = dialog.getTopComponent();
                    return form.checkPreventClose === undefined
                        || form.checkPreventClose() !== false;
                },
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
                deleteConfirmation: __('Вы уверены, что хотите удалить эту специализацию?'),
                deleted: __('Специализация была успешно удалена'),
                created: __('Специализация была успешно добавлена'),
                updated: __('Специализация была успешно обновлена'),
            };
        },
        removeSpecialization() {
            if(this.activeItem && this.activeItem.clinics.length != 0) {
                return this.$error(__('Удаление невозможно, есть связанные клиники'));
            }
            return this.remove();
        },
    },
}
</script>
