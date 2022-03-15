<template>
    <price-form
        :model="model"
        :filter="filter"
        :price-sets="priceSets"
        :limit-clinics="$isUpdateLimited(premissions)">
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
                {{ __('Обновить') }}
            </el-button>
        </div>
    </price-form>
</template>

<script>
import PriceForm from './Form.vue';
import Service from '@/models/service';
import Analysis from '@/models/analysis';
import Price from '@/models/price';

export default {
    components: {
        PriceForm,
    },
    props: {
        subject: Object,
        item: Object,
        premissions: String,
        priceSets: Array,
    },
    data() {
        return {
            model: new Price({id: this.item.id}),
            filter: this.getFilter(),
        };
    },
    mounted() {
        this.model.fetch(['user', 'permissions']);
    },
    methods: {
        getFilter() {
            if(this.subject instanceof Service) {
                return {has_service: this.subject.id};
            }
            if(this.subject instanceof Analysis) {
                return {has_analysis: this.subject.id};
            }
            return {};
        },
        update() {
            this.$clearErrors();
            this.model.save().then((response) => {
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
        cancel() {
            this.$emit('cancel');
        },
    }
}
</script>
