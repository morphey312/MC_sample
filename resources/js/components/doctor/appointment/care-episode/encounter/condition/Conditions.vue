<template>
    <div class="conditions-container">
        <conditions-list
            ref="table"
            v-loading="loading"
            :conditions="conditions"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <el-button
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                :disabled="activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
        <slot name="buttons"></slot>
    </div>
</template>

<script>
import ManageMixin from '@/mixins/manage';
import ConditionsList from './List.vue';
import CreateCondition from './CreateForm';
import EditCondition from './EditForm';

export default {
    components: {
        ConditionsList,
    },
    props: {
        encounter: Object,
        outpatientRecord: Object,
    },
    mixins: [
        ManageMixin,
    ],
    watch: {
        conditions() {
            this.$nextTick(() => {
                this.refresh();
            });
        },
        ['encounter.id']() {
            this.getConditions()
        }
    },
    beforeMount() {
        this.getConditions();
    },
    data() {
        return {
            loading: true,
            conditions: [],
        }
    },
    methods: {
        getConditions() {
            let model = this.encounter;
            model.fetch(['conditions']).then(() => {
                this.conditions = model.conditions;
                this.loading = false;
            });
        },
        getModalOptions() {
            return {
                createForm: CreateCondition,
                createProps: {
                    encounter: this.encounter,
                    outpatientRecord: this.outpatientRecord,
                },
                editForm: EditCondition,
                editProps: {
                    item: this.activeItem,
                    outpatientRecord: this.outpatientRecord,
                },
                createHeader: __('Добавить состояние пациента'),
                editHeader: __('Изменить состояние пациента'),
                width: '800px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить запись?'),
                deleted: __('Запись успешно удалена'),
                created: __('Запись успешно добавлена'),
                updated: __('Запись успешно обновлена'),
            };
        },
        onCreated(condition) {
            this.conditions = [
                ...this.conditions,
                {
                    id: condition.id,
                    data: condition,
                },
            ];
        },
        onUpdated(condition) {
            this.conditions = this.conditions.map((e) => {
                if (e.data.id === model.id) {
                    return {
                        id: condition.id,
                        data: condition,
                    };
                }

                return e;
            });
        },
        onDeleted(condition) {
            this.conditions = this.conditions.filter((e) => e.id !== condition.id);
        },
    },
}
</script>
