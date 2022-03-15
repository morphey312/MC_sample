<template>
    <div class="create-clinics sections-wrapper">
        <section>
            <clinic-form 
                :model="model"
                :legal-entity="legalEntity">
                <div 
                    slot="buttons"
                    class="mt-20">
                    <el-button @click="add">
                        {{ __('Добавить клинику') }}
                    </el-button>
                </div>
            </clinic-form>
        </section>
        <template v-if="hasClinics">
            <hr />
            <section>
                <legal-entity-clinics-list 
                    ref="table"
                    :legal-entity="repo"
                    @selection-changed="setActiveItem"
                    @loaded="refreshed" />
                <div class="mt-20">
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
            </section>
        </template>
        <div class="dialog-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                v-if="hasClinics"
                type="primary"
                @click="complete">
                {{ __('Завершить') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import LegalEntity from '@/models/legal-entity';
import EntityClinic from '@/models/legal-entity/clinic';
import ClinicForm from './clinics/Form.vue';
import FormEdit from './clinics/FormEdit.vue';
import LegalEntityClinicsList from './clinics/List.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ClinicForm,
        LegalEntityClinicsList,
    },
    props: {
        legalEntity: Object,
    },
    data() {
        return {
            hasClinics: false,
            model: new EntityClinic({
                legal_entity_id: this.legalEntity.id,
            }),
            repo: new LegalEntity({
                id: this.legalEntity.id,
            }),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        add() {
            this.saveClinic(() => {
                this.model = new EntityClinic({
                    legal_entity_id: this.legalEntity.id,
                });
                this.$info(__('Корпоративный клиент успешно добавлен в клинику'));
                this.hasClinics = true;
                this.refresh();
            });
        },
        saveClinic(then) {
            this.$clearErrors();
            this.model.save().then((response) => {
                then();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        complete() {
            this.$emit('completed');
        },
        getModalOptions() {
            return {
                editForm: FormEdit,
                editProps: () => ({legalEntity: this.legalEntity}),
                editHeader: __('Изменить корпоративного клиента в клинике'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                updated: __('Данные корпоративного клиента в клинике были успешно обновлены'),
            };
        },
    },
}
</script>