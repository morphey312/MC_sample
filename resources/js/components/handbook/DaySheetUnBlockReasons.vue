<template>


                <reasons-unblock
                    ref="table"

                    @selection-changed="setActiveItem"
                    @loaded="refreshed">
                    <div class="buttons" slot="buttons">
                        <el-button
                            v-if="$can('day-sheet-time-unblock-reasons.create')"
                            @click="create" >
                            {{ __(' Добавить') }}
                        </el-button>
                        <el-button
                            v-if="$can('day-sheet-time-unblock-reasons.update')"
                            :disabled="activeItem === null"
                            @click="edit" >
                            {{ __('Редактировать') }}
                        </el-button>
                        <el-button
                            v-if="$can('day-sheet-time-unblock-reasons.delete')"
                            :disabled="activeItem === null"
                            @click="remove">
                            {{ __('Удалить') }}
                        </el-button>
                    </div>
                </reasons-unblock>

</template>

<script>
import ReasonsUnblock from './reason-unblock/List.vue';
import FormCreate from './reason-unblock/FormCreate.vue';
import FormEdit from './reason-unblock/FormEdit.vue';

import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ReasonsUnblock,
    },

    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить причину разблокировки'),
                editHeader: __('Редактировать причину разблокировки'),
                width: '400px',
            };
        },

        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту причину разблокировки?'),
                deleted: __('Причина разблокировки успешно удалена'),
                created: __('Причина разблокировки успешно добавлена'),
                updated: __('Причина разблокировки успешно обновлена'),
            };
        }
    }
}
</script>
