<template>
    <div class="create-clinics sections-wrapper">
        <section>
            <clinic-form 
                :model="model"
                :limit-clinics="$isCreationLimited('employees')">
                <div 
                    slot="buttons"
                    class="mt-20">
                    <el-button
                        @click="add">
                        {{ __('Добавить клинику') }}
                    </el-button>
                </div>
            </clinic-form>
        </section>
        <template v-if="hasClinics">
            <hr />
            <section>
                <clinics-list 
                    ref="table"
                    :employee="repo"
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
                <slot name="buttons"></slot>
            </section>
        </template>
    </div>
</template>

<script>
import Employee from '@/models/employee';
import EmployeeClinic from '@/models/employee/clinic';
import ClinicForm from './clinics/Form.vue';
import FormEdit from './clinics/FormEdit.vue';
import ClinicsList from './clinics/List.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ClinicForm,
        ClinicsList,
    },
    props: {
        employee: Object,
    },
    data() {
        return {
            hasClinics: false,
            model: new EmployeeClinic({
                employee_id: this.employee.id,
            }),
            repo: new Employee({
                id: this.employee.id,
            }),
        };
    },
    methods: {
        add() {
            this.saveClinic(() => {
                this.model = new EmployeeClinic({
                    employee_id: this.employee.id,
                });
                this.$info(__('Сотрудник успешно добавлен в клинику'));
                this.hasClinics = true;
                this.$emit('created');
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
        getModalOptions() {
            return {
                editForm: FormEdit,
                editProps: {
                    limitClinics: this.$isCreationLimited('employees'),
                },
                editHeader: __('Изменить сотрудника в клинике'),
                width: '900px',
            };
        },
        getMessages() {
            return {
                updated: __('Данные сотрудника в клинике были успешно обновлены'),
            };
        },
    },
}
</script>
