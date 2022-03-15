<template>
    <clinic-form 
        :model="model"
        :workspace="workspace">
        <div 
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </clinic-form>    
</template>

<script>
import WorkspaceClinic from '@/models/workspace/clinic';
import ClinicForm from './Form.vue';

export default {
    components: {
        ClinicForm
    },
    props: {
        workspace: Object,
    },
    data() {
        return {
            model: new WorkspaceClinic({
                workspace_id: this.workspace.id,
            }),
        }
    },
    mounted() {
        this.model.setParent(this.workspace);
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('created', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },
}
</script>
