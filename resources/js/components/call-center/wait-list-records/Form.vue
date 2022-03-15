<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <div class="manage-table">
                    <record-details :record="model" />
                </div>
            </section>
            <hr />
            <section class="grey">
                <el-row :gutter="20">
                    <el-col :span="8">
                        <form-select
                            :entity="model"
                            :repository="operators"
                            property="operator_id"
                            :label="__('Оператор')"
                        />
                    </el-col>
                </el-row>
            </section>
        </div>
        <slot name="buttons"></slot>
    </model-form>
</template>

<script>
import EmployeeRepository from '@/repositories/employee';
import RecordDetails from './Details.vue';
import CONSTANTS from '@/constants';

export default {
    components: {
        RecordDetails,
    },
    props: {
        model: {
            type: Object,
        },
    },
    data() {
        return {
            operators: new EmployeeRepository({
                filters: {positionType: CONSTANTS.EMPLOYEE.POSITIONS.OPERATOR}
            }),
        }
    },
}
</script>