<script>
import AnalysesUpload from '@/components/price-list/upload/Analyses.vue';
import TopForm  from './Top.vue';
import BatchRequest from '@/services/batch-request';
import PriceUploadMixin from './mixin/price-upload';

export default {
    extends: AnalysesUpload,
    mixins: [
        PriceUploadMixin,
    ],
    components: {
        TopForm,
    },
    methods: {
        getNewServiceWarning() {
            return __('Анализ не будет создан, не найдено соответствия');
        },
        getDisabledServiceWarning() {
            return __('Анализ неактивен, он не будет вновь активирован');
        },
        getPricesBatchRequest() {
            return new BatchRequest('/api/v1/insurance/prices/analyses-batch');
        },
    },
}
</script>