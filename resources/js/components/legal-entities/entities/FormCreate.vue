<template>
    <el-tabs v-model="activeTab" class="tab-group-beige sections-wrapper">
        <el-tab-pane
            :lazy="true"
            :label="__('Данные о корп. клиенте')"
            name="info">
            <section>
                <entity-form :model="model">
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
                </entity-form>
            </section>
        </el-tab-pane>
        <el-tab-pane
            :lazy="true"
            :disabled="model.isNew()"
            :label="__('Клиники компании')"
            name="clinics">
            <section>
                <entity-clinics 
                    :legal-entity="model"
                    @cancel="cancel"
                    @completed="completed" />
            </section>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import LegalEntity from '@/models/legal-entity';
import EntityForm from './Form.vue';
import EntityClinics from './CreateClinics.vue';

export default {
    components: {
        EntityForm,
        EntityClinics,
    },
    data() {
        return {
            model: new LegalEntity(),
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
                this.$info(__('Данные корпоративного клиента успешно сохранены'));
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