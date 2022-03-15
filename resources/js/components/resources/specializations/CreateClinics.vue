<template>
    <div class="create-clinics sections-wrapper">
        <section>
            <clinic-form :model="model">
                <div slot="buttons">
                    <el-button @click="add">
                        {{ __('Добавить') }}
                    </el-button>
                </div>
            </clinic-form>
        </section>
        <template v-if="hasClinics">
            <hr />
            <section>
                <specialization-clinics-list 
                    ref="table"
                    :specialization="repo"
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
import ClinicForm from './clinics/Form.vue';
import FormEdit from './clinics/FormEdit.vue';
import SpecializationClinicsList from './clinics/List.vue';
import Specialization from '@/models/specialization';
import SpecializationClinic from '@/models/specialization/clinic';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ClinicForm,
        SpecializationClinicsList,
    },
    props: {
        specialization: Object,
    },
    data() {
        return {
            hasClinics: false,
            model: new SpecializationClinic({
                specialization_id: this.specialization.id,
            }),
            repo: new Specialization({
                id: this.specialization.id,
            }),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        add() {
            this.saveClinic(() => {
                this.model = new SpecializationClinic({
                    specialization_id: this.specialization.id,
                });
                this.$info(__('Специализация успешно добавлена в клинику'));
                this.hasClinics = true;
                this.refresh();
            });
        },
        saveClinic(then) {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('clinics-changed', this.getClinics());
                then();
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        getClinics() {
            let table = this.$refs.table;
            return this.$refs.table ? this.$refs.table.model.clinics : [];
        },
        complete() {
            this.$emit('completed');
        },
        getModalOptions() {
            return {
                editForm: FormEdit,
                editHeader: __('Изменить специализацию в клинике'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                updated: __('Данные специализации в клинике были успешно обновлены'),
            };
        },
    },
}   
</script>