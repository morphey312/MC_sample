<template>
    <price-form
        :model="model"
        :filter="filter"
        :price-sets="priceSets"
        :limit-clinics="$isCreationLimited(premissions)">
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
                {{ __('Добавить') }}
            </el-button>
        </div>
    </price-form>
</template>

<script>
import PriceForm from './Form.vue';
import Price from '@/models/price';
import Analysis from '@/models/analysis';
import Service from '@/models/service';

export default {
    components: {
        PriceForm,
    },
    props: {
        subject: Object,
        setId: Number,
        priceSets: Array,
        premissions: String,
    },
    data() {
        return {
            model: new Price({
                set_id: this.setId,
            }),
            filter: this.getFilter(),
        }
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
        create() {
            this.$clearErrors();
            this.model.createFor(this.subject).then((response) => {
                this.$emit('created', this.model);
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
