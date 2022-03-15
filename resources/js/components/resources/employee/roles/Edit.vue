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
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </role-form>
</template>

<script>
import RoleForm from './Form.vue';
import Role from '@/models/role';
import EditMixin from '@/mixins/generic-edit';
import FormMixin from './mixins/form';

export default {
    mixins: [
        EditMixin,
        FormMixin,
    ],
    components: {
        RoleForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: new Role({id: this.item.id}),
        };
    },
    mounted() {
        this.model.fetch(['permissions', 'users']);
    },
}
</script>