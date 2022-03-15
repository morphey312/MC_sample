<template>
    <section>
        <policy-list 
            v-if="patient !== null"
            ref="table"
            :patient="patient"
            @loaded="refreshed"
            @selection-changed="setActiveItem"
        />
        <div class="mt-10">
            <el-button
                v-if="$can('insurance-policies.create')"
                :disabled="patient === null || patient.loading"
                @click="create">
                {{ __('Добавить полис') }}
            </el-button>
            <el-button
                v-if="$can('insurance-policies.update')"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$can('insurance-policies.delete')"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </section>
</template>
<script>
import PolicyList from '@/components/patients/insurance-policies/List.vue';
import FormCreate from '@/components/patients/insurance-policies/FormCreate.vue';
import FormEdit from '@/components/patients/insurance-policies/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        PolicyList
    },
    props: {
        patient: Object,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    patient: this.patient,
                },
                editForm: FormEdit,
                editProps: {
                    patient: this.patient,
                    item: this.activeItem,
                },
                createHeader: __('Добавить страховой полис пациенту'),
                editHeader: __('Изменить страховой полис пациента'),
                width: '510px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот полис?'),
                deleted: __('Полис был успешно удален'),
                created: __('Полис был успешно добавлен'),
                updated: __('Полис был успешно обновлен'),
            };
        },
        onCreated(policy) {
            this.$emit('add-policy', policy);
        },
        onUpdated(policy) {
            this.$emit('update-policy', policy);
        },
        onDelete() {
            this.$emit('delete-policy', this.getSelectionIndex());
            this.setActiveItem([]);
        },
        remove() {
            this.$confirm(__('Вы уверены, что хотите удалить этот полис?'), () => {
                if (this.activeItem.isNew()) {
                    return this.onDelete();
                }

                return this.getIsDeleteable().then((response) => {
                    if (response == false) {
                        return this.$error(__('Полис не может быть удален, есть связанная запись'));
                    }
                    this.onDelete();
                });
            });
        },
        getIsDeleteable() {
            return this.activeItem.isDeleteable().then((response) => {
                return Promise.resolve(true);
            }).catch((e) => {
                return Promise.resolve(false);
            });
        },
        getSelectionIndex() {
            return this.$refs.table.selectedIndex;
        },
    }
}
</script>