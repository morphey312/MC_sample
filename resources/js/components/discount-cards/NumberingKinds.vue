<template>
    <page
        :title="__('Виды нумерации')"
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
                <card-numbering-kinds-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters"  />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
            <numbering-list
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @loaded="refreshed"
                @header-filter-updated="syncFilters"
                @syncFilters="syncFilters">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$canCreate('card-numbering-kinds')"
                        @click="create">
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$canUpdate('card-numbering-kinds')"
                        :disabled="activeItem === null"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$canDelete('card-numbering-kinds')"
                        :disabled="activeItem === null"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </numbering-list>
        </section>
    </page>
</template>

<script>
import NumberingList from './numbering-kinds/List.vue';
import CreateNumbering from './numbering-kinds/FormCreate.vue';
import CardNumberingKindsFilter from './numbering-kinds/Filter.vue';
import EditNumbering from './numbering-kinds/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin
    ],
    components: {
        CardNumberingKindsFilter,
        NumberingList,
    },
    data(){
        return {
            displayFilter: true,
        }
    },
    methods: {
        getModalOptions() {
            return {
                createForm: CreateNumbering,
                editForm: EditNumbering,
                createHeader: __('Добавить вид нумерации'),
                editHeader: __('Изменить вид нумерации'),
                width: '900px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот вид нумерации?'),
                deleted: __('Вид нумерации был успешно удален'),
                created: __('Вид нумерации был успешно добавлен'),
                updated: __('Вид нумерации был успешно обновлен'),
            };
        },
    },
};
</script>
