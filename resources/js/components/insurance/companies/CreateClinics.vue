<template>
    <div class="create-clinics sections-wrapper">
        <section>
            <clinic-form 
                :model="model"
                :company="company">
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
                <company-clinics-list 
                    ref="table"
                    :company="repo"
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
import InsuranceCompany from '@/models/insurance-company';
import CompanyClinic from '@/models/insurance-company/clinic';
import ClinicForm from './clinics/Form.vue';
import FormEdit from './clinics/FormEdit.vue';
import CompanyClinicsList from './clinics/List.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ClinicForm,
        CompanyClinicsList,
    },
    props: {
        company: Object,
    },
    data() {
        return {
            hasClinics: false,
            model: new CompanyClinic({
                insurance_company_id: this.company.id,
            }),
            repo: new InsuranceCompany({
                id: this.company.id,
            }),
        };
    },
    methods: {
        cancel() {
            this.$emit('cancel');
        },
        add() {
            this.saveClinic(() => {
                this.model = new CompanyClinic({
                    insurance_company_id: this.company.id,
                });
                this.$info(__('Компания успешно добавлена в клинику'));
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
                editProps: () => ({company: this.company}),
                editHeader: __('Изменить компанию в клинике'),
                width: '770px',
            };
        },
        getMessages() {
            return {
                updated: __('Данные компании в клинике были успешно обновлены'),
            };
        },
    },
}
</script>