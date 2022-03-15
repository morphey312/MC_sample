<template>
<condition-form
        :model="model"
        :outpatientRecord="outpatientRecord">
        <div
            slot="buttons"
            class="form-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="update">
                {{ __('Сохранить') }}
            </el-button>
            <el-button
                type="primary"
                @click="createAndSubmit">
                {{ __('Создать и отправить в e-Health') }}
            </el-button>
        </div>
    </condition-form>
</template>

<script>
import ConditionForm from './Form.vue';
import EditMixin from '@/mixins/generic-edit';

export default {
    mixins: [
        EditMixin,
    ],
    components: {
        ConditionForm,
    },
    props: {
        item: Object,
        outpatientRecord: Object,
    },
    data() {
        return {
            model: this.item.clone(),
        };
    },
    methods: {
        createAndSubmit() {

        },
    },
}
</script>
