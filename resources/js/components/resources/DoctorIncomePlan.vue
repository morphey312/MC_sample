<template>
    <page
        :title="__('План прихода по врачам')"
        type="flex">
        <template
            slot="header-addon">
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
                <income-plan-filter
                    ref="filter"
                    :initial-state="filters"
                     @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="shrinkable grey-cap">
            <income-plan-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('doctor-income-plans.access-clinic')"
                        @click="create" >
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$can('doctor-income-plans.access-clinic')"
                        :disabled="activeItem === null || !activeItem.id"
                        @click="edit" >
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$can('doctor-income-plans.access-clinic')"
                        :disabled="activeItem === null || !activeItem.id"
                        @click="showLog" >
                        {{ __('Операции') }}
                    </el-button>
                </div>
            </income-plan-list>
        </section>
    </page>
</template>

<script>
import IncomePlanList from './doctor-income-plan/List.vue';
import IncomePlanFilter from './doctor-income-plan/Filter.vue';
import FormCreate from './doctor-income-plan/FormCreate.vue';
import FormEdit from './doctor-income-plan/FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import PlanLog from '@/components/action-log/employee/DoctorIncomePlan.vue';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        IncomePlanList,
        IncomePlanFilter,
    },
    methods: {
        getDefaultFilters() {
            return {
                clinic: this.getLoggedUserClinics(),
                
            };
        },
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить сотрудника'),
                editHeader: __('Редактировать норму'),
                width: '900px',
            };
        },
        getMessages() {
            return {
                created: __('Норма успешно добавлена'),
                updated: __('Норма успешно обновлена'),
            };
        },
        showLog() {
            this.$modalComponent(PlanLog, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения плана врача'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    }
}
</script>