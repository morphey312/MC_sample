<template>
    <role-form 
        :model="model"
        :tab="activeTab"
        @tab-changed="changeTab">
        <div 
            slot="buttons" 
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
        </div>
    </role-form>
</template>

<script>
import RoleForm from './Form.vue';
import Role from '@/models/role';
import CreateMixin from '@/mixins/generic-create';
import FormMixin from './mixins/form';

export default {
    mixins: [
        CreateMixin,
        FormMixin,
    ],
    components: {
        RoleForm,
    },
    data() {
        return {
            model: new Role(),
        };
    },
}
</script>