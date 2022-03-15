<template>
    <card-form 
        ref="cardForm"
        :model="model"
        :patient="patient" >
        <div 
            slot="buttons" 
            class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Создать') }}
            </el-button>
        </div>
    </card-form>
</template>

<script>
import IssuedCard from '@/models/patient/issued-discount-card';
import CardForm from './Form.vue';
import DiscountCardMixin from '@/components/patients/mixins/discount-card';

export default {
    mixins: [
        DiscountCardMixin,
    ],
    components: {
        CardForm,
    },
    props: {
        patient: Object,
    },
    data() {
        let today = this.$moment().format('YYYY-MM-DD');
        let initAttributes = {
            issued: today, 
            valid_from: today, 
            expires: today
        };

        return {
            model: new IssuedCard(initAttributes),
        };
    },
    methods: {
        create() {
            this.$clearErrors();
            this.model.validate().then((errors) => {
                if (_.isEmpty(errors)) {
                    if (this.isInvalidMaxExpire()) {
                        return this.expireError();
                    }

                    if (this.hasSameCard()) {
                        return this.crossError();
                    }

                    this.$info(__('Карта была успешно добавлена'));
                    this.$emit('created', this.model); 
                    return;
                }

                return this.$displayErrors({errors});
            });
        },
    },
};
</script>