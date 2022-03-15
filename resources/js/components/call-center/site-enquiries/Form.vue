<template>
    <model-form :model="model">
        <div class="sections-wrapper">
            <section>
                <div class="manage-table">
                    <enquiry-details :enquiry="model" />
                </div>
            </section>
            <section>
                <div class="site-enquiry mt-20" v-if="hasServices || hasUnpaidServices">
                    <h3>{{ __('Выбранные услуги') }}</h3>
                    <service-list :enquiry="model"/>
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
import EnquiryDetails from './Details.vue';
import CONSTANTS from '@/constants';
import ServiceList from './ServiceList.vue';

export default {
    components: {
        EnquiryDetails,
        ServiceList
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
    computed: {
        hasServices() {
            return this.model.service_list.length !== 0 || this.model.analysis_list.length !== 0;
        },
        hasUnpaidServices() {
            return this.model.unpaid_service_list.length !== 0 || this.model.unpaid_analysis_list.length !== 0;
        },
    }
}
</script>
