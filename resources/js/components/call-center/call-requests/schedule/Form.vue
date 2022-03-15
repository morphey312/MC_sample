<template>
    <model-form :model="model">
        <form-select
            :entity="model"
            :options="purposes"
            property="call_request_purpose_id"
            :label="__('Цель прозвона')"
        />
        <form-row
            name="dates"
            :label="__('Желаемый период для прозвона')">
            <div class="form-input-group">
                <form-date
                    :entity="model"
                    property="recall_from"
                    label=""
                />
                <form-date
                    :entity="model"
                    property="recall_to"
                    label=""
                />
            </div>
        </form-row>
        <form-text
            :entity="model"
            property="comment"
            :label="__('Примечание')"
        />
        <slot name="buttons"></slot>      
    </model-form>
</template>

<script>
import CallRequestPurposeRepository from '@/repositories/call-request/purpose';

export default {
    props: {
        model: {
            type: Object
        },
    },
    data() {
        return {
            period: {},
            purposes: [],
        }
    },
    mounted() {
        this.getPurposes();
    },
    methods: {
        getPurposes() {
            let purpose = new CallRequestPurposeRepository();
            purpose.fetchList().then((response) => {
                this.purposes = response;
            });
        },
    }
};
</script>
