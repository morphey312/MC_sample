<template>
    <section v-loading="loading">
        <form-row name="card_templates" v-if="!loading">
            <transfer-table
                ref="transfer"
                key="card_templates"
                :items="cardTemplates"
                v-model="model.additional_templates"
                :left-title="__('Шаблон')"
                left-width="310px"
                :right-title="__('Шаблон')"
                right-width="310px">
            </transfer-table>
        </form-row>
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click.prevent="updateModel">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </section>
</template>
<script>
import RecordTemplateRepository from '@/repositories/patient/card/record-template';

export default {
    props: {
        model: Object,
    },
    data() {
        return {
            cardTemplates: [],
            loading: false,
        }
    },
    mounted() {
        this.getTemplates();
    },
    methods: {
        getTemplates() {
            this.loading = true;
            this.cardTemplates = [];
            let repo = new RecordTemplateRepository();
            repo.fetchList(null, [{field: 'name', direction: 'asc'}]).then((response) => {
                this.cardTemplates = response;
                this.loading = false;
            });
        },
        cancel() {
            this.$emit('cancel');
        },
        updateModel() {
            this.$emit('specialization-updated');
        },
    },
}
</script>