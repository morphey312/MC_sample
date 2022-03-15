<template>
    <task-form 
        :model="model"
        :limit-clinics="$isCreationLimited('personal-tasks')">
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
                @click="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </task-form>
</template>

<script>
import PersonalTask from '@/models/personal-task';
import TaskForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        TaskForm
    },
    data() {
        return {
            model: new PersonalTask(),
        }
    },
}
</script>
