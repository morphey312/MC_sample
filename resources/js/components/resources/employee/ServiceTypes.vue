<template>
    <section class="services-container">
        <service-type-list 
            ref="table"
            :employee="employee"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <el-button
                v-if="$can('employee-service-types.create')"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$can('employee-service-types.delete')"
                :disabled="activeItem === null || activeItem.is_deleted"
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
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ServiceTypeList,
    },
    props: {
        employee: Object,
        modalComponent: Object,
        mspType: String,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    employee: this.employee,
                },
                createHeader: __('Добавить роль'),
                width: '500px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту роль? Данная операция необратима.'),
                deleted: __('Роль была успешно удалена'),
                created: __('Роль была успешно создана'),
            };
        },
    },
}
</script>