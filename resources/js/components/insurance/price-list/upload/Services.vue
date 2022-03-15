<script>
import ServiceUpload from '@/components/price-list/upload/Services.vue';
import TopForm  from './Top.vue';
import BatchRequest from '@/services/batch-request';
import PriceUploadMixin from './mixin/price-upload';

export default {
    extends: ServiceUpload,
    mixins: [
        PriceUploadMixin,
    ],
    components: {
        TopForm,
    },
    methods: {
        getNewServiceWarning() {
            return __('Услуга не будет создана, не найдено соответствия');
        },
        getDisabledServiceWarning() {
            return __('Услуга неактивна, она не будет вновь активирована');
        },
        getPricesBatchRequest() {
            return new BatchRequest('/api/v1/insurance/prices/services-batch');
        },
    },
}
</script>