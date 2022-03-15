import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    props: {
        elasticSearchClient: Object,
        activeStatuses: Array,
        appointmentIndex: String,
        paymentIndex: String,
    },
}