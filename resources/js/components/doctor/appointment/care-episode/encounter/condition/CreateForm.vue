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
                @click="create">
                {{ __('Создать') }}
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
import Condition from '@/models/ehealth/encounter/condition';
import ConditionForm from './Form.vue';
import CreateMixin from '@/mixins/generic-create';

export default {
    mixins: [
        CreateMixin,
    ],
    components: {
        ConditionForm,
    },
    props: {
        encounter: Object,
        outpatientRecord: Object,
    },
    data() {
        return {
            model: new Condition({
                encounter_id: this.encounter.id,
            }),
        }
    },
    methods: {
        createAndSubmit() {

        },
    },
}
</script>
