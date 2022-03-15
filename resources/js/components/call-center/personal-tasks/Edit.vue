<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :lazy="true"
            :label="__('Детали задания')"
            name="info">
            <section>
                <task-form 
                    :model="model"
                    :limit-clinics="$isUpdateLimited('personal-tasks')">
                    <div 
                        slot="buttons" 
                        slot-scope="data"
                        class="form-footer text-right">
                        <el-button @click="cancel">
                            {{ __('Отменить') }}
                        </el-button>
                        <el-button
                            type="primary"
                            :disabled="data.countUploads !== 0"
                            @click="update">
                            {{ __('Сохранить') }}
                        </el-button>
                    </div>
                </task-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :disabled="isNew(model)"
            :label="__('Обратная связь')"
            name="feedback">
            <section>
                <task-feedback :model="item" />
                <div class="form-footer text-right">
                    <el-button @click="cancel">
                        {{ __('Отменить') }}
                    </el-button>
                    <el-button
                        type="primary"
                        :disabled="true">
                        {{ __('Сохранить') }}
                    </el-button>
                </div>
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import TaskForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';
import TaskFeedback from './TaskFeedback.vue';
import CONSTANT from '@/constants';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        TaskForm,
        TaskFeedback,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
            activeTab: this.item.status === CONSTANT.PERSONAL_TASK.STATUS.COMPLETED ? 'feedback' : 'info',
        };
    },
    methods: {
        isNew(task) {
            return task.status === CONSTANT.PERSONAL_TASK.STATUS.NEW;
        },
    },
}
</script>
