<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :lazy="true"
            :label="__('Общие')"
            name="info">
            <section>
                <workspace-form :model="model">
                    <div 
                        slot="buttons"
                        class="form-footer text-right">
                        <el-button @click="cancel">
                            {{ __('Отменить') }}
                        </el-button>
                        <el-button
                            v-if="model.isNew()"
                            type="primary"
                            @click="create">
                            {{ __('Далее') }}
                        </el-button>
                    </div>
                </workspace-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :disabled="model.isNew()"
            :label="__('Клиники')"
            name="clinics">
            <section>
                <workspace-clinics 
                    :workspace="model"
                    @cancel="cancel"
                    @completed="completed" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import Workspace from '@/models/workspace';
import WorkspaceForm from './Form.vue';
import WorkspaceClinics from './CreateClinics.vue';

export default {
    components: {
        WorkspaceForm,
        WorkspaceClinics,
    },
    data() {
        return {
            model: new Workspace(),
            activeTab: 'info',
        }
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        create() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$info(__('Данные кабинета успешно сохранены'));
                this.activeTab = 'clinics';
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        completed() {
            this.$emit('created', this.model);
        },
    },
}
</script>
