<template>
    <enquiry-form 
        :model="model">
        <div slot="buttons" class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отмена') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </enquiry-form>
</template>

<script>
import EnquiryForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        EnquiryForm,
    },
    props: {
        item: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
}
</script>
