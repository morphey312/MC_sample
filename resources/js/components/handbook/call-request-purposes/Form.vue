<template>
    <form>
        <section>
            <el-row :gutter="20">
                <el-col :span="12">
                    <form-input-i18n
                        :entity="model"
                        property="name"
                        :label="__('Цель прозвона')" />
                    <form-checkbox
                        :entity="model"
                        property="manual_add"
                        :label="__('Цель для оператора по умолчанию (ручное добавления заявки на прозвон)')"
                    />
                </el-col>
                <el-col :span="12">
                    <form-row
                        name="auto_add_request"
                        label="&nbsp">
                        <form-checkbox
                            :entity="model"
                            property="auto_add"
                            :label="__('Цель для автоматического добавления заявок по первичным пациентам')"
                        />
                    </form-row>
                    <form-checkbox
                        :entity="model"
                        property="auto_next_visit"
                        :label="__('Цель для автоматического добавления заявок на повторную запись по установленной врачом даты рекомендованного визита')"
                    />
                </el-col>
            </el-row>
        </section>
        <hr class="grey">
        <section>
            <form-row name="call-purposes">
                <transfer-table
                    v-if="model.loading === false"
                    key="call-purposes"
                    :items="call_results"
                    v-model="model.call_results"
                    :left-title="__('Результаты звонков')"
                    left-width="320px"
                    :right-title="__('Выбранные результаты звонков')"
                    right-width="320px"
                    :emptySelectionMessage="placeholder">
                </transfer-table>
            </form-row>
            <slot name="buttons"></slot>
        </section>
    </form>
</template>

<script>
import CallResultRepository from '@/repositories/calls/result';

export default {
    props: {
        model: Object
    },
    data() {
        return {
            call_results: [],
            placeholder: this.getPlaceholder(),
        }
    },
    mounted() {
        this.getLists();
    },
    methods: {
        getLists() {
            let callResult = new CallResultRepository();
            callResult.fetchList().then((response) => {
                this.call_results = response;
            });
        },
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Выберите и добавьте результаты звонков слева'));
        },
    }
}
</script>
