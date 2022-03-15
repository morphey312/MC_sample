<template>

            <reasons-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('day-sheet-time-block-reasons.create')"
                        @click="create" >
                        {{ __(' Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$can('day-sheet-time-block-reasons.update')"
                        :disabled="activeItem === null"
                        @click="edit" >
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$can('day-sheet-time-block-reasons.delete')"
                        :disabled="activeItem === null"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </reasons-list>

</template>

<script>
import ReasonsList from './day-sheet-block-reason/List.vue';
import FormCreate from './day-sheet-block-reason/FormCreate.vue';
import FormEdit from './day-sheet-block-reason/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ReasonsList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить причину блокировки'),
                editHeader: __('Редактировать причину блокировки'),
                width: '400px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту причину блокировки?'),
                deleted: __('Причина блокировки успешно удалена'),
                created: __('Причина блокировки успешно добавлена'),
                updated: __('Причина блокировки успешно обновлена'),
            };
        }
    }
}
</script>
