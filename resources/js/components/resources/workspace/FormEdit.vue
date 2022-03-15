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
                            type="primary"
                            @click="update">
                            {{ __('Сохранить') }}
                        </el-button>
                    </div>
                </workspace-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :label="__('Клиники')"
            name="clinics">
            <section>
                <workspace-clinics 
                    :workspace="model"
                    :modal-component="modalComponent"
                    @cancel="cancel"
                    @workspace-updated="update"
                    @clinics-updated="clinicsUpdated" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import WorkspaceForm from './Form.vue';
import WorkspaceClinics from './Clinics.vue';
import Workspace from '@/models/workspace';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        WorkspaceForm,
        WorkspaceClinics,
    },
    props: {
        item: Object,
        modalComponent: Object,
    },
    data() {
        return {
            model: new Workspace({id: this.item.id}),
            activeTab: 'info',
        };
    },
    mounted() {
        this.model.fetch();
    },
    methods: {
        clinicsUpdated() {
            this.$emit('clinicsUpdated');
        },
    },
}
</script>