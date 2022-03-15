<template>
    <div>
        <reason-form :model="model">
            <div 
                slot="buttons"
                class="form-footer text-right">
                <el-button @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    @click.prevent="update">
                    {{ __('Сохранить') }}
                </el-button>
            </div>
        </reason-form>
    </div>
</template>

<script>
import Reason from '@/models/appointment/status/reason';
import ReasonForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    props: {
        item: Object
    },
    components: {
        ReasonForm,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
}
</script>