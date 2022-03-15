<template>
    <section class="services-container">
        <service-type-list 
            ref="table"
            :clinic="clinic"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <el-button
                v-if="$can('service-types.create')"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$can('service-types.update')"
                :disabled="activeItem === null"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$can('service-types.delete')"
                :disabled="activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
        <slot name="buttons"></slot>
    </section>
</template>

<script>
import ServiceTypeList from './service-types/List.vue';
import FormCreate from './service-types/FormCreate.vue';
import FormEdit from './service-types/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ServiceTypeList,
    },
    props: {
        clinic: Object,
        modalComponent: Object,
        mspType: String,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    clinic: this.clinic,
                },
                editForm: FormEdit,
                editProps: {
                    clinic: this.clinic,
                },
                createHeader: __('Создать вид услуг'),
                editHeader: __('Обновить вид услуг'),
                width: '800px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку видов услуг'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот вид услуг?'),
                deleted: __('Вид услуг был успешно удален'),
                created: __('Вид услуг был успешно создан'),
                updated: __('Вид услуг был успешно обновлен'),
            };
        },
    },
}
</script>