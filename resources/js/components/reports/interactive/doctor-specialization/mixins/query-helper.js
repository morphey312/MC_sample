import ManageMixin from '@/mixins/manage';
import CONSTANTS from "@/constants";
import ChartFormatMixin from "@/components/reports/interactive/mixins/chart-format";

export default {
    mixins: [
        ManageMixin,
        ChartFormatMixin
    ],
    props: {
        clinics: Array,
        specializations: Array,
        elasticSearchClient: Object,
        reportIndex: String,
    },
    data() {
        return {
            tableData: [],
        }
    },
    methods: {
        prepareFilters() {
            let filters = {...this.filters};

            if (!this.filters.specialization || this.filters.specialization.length === 0) {
                filters.specialization = this.specialization.map(c => c.id);
            }
            return filters;
        },
        getFirst(elasticData,doctorId) {
            let firsts = _.filter(elasticData, {'is_first': 1,'doctor_id': doctorId});
            let totalSum = 0

            for (let item of firsts) {
                if (item.service_payment_destination_id === 3) {
                    totalSum++
                }
            }

            return totalSum
        },
        getSecond(elasticData,doctorId) {

            let seconds = _.filter(elasticData, {'is_first': 0,'doctor_id': doctorId});
            let totalSum = 0

            for (let item of seconds) {
                if (item.service_payment_destination_id === 3) {
                    totalSum++
                }
            }

            return totalSum
        },
        getConsultationsPayments(elasticData,doctorId) {
            let consultations = _.filter(elasticData, {'service_payment_destination': "Консультація",'doctor_id': doctorId});
            let totalSum = 0
            for (let item of consultations) {
                totalSum += item.payed_amount
            }

            return totalSum
        },
        getAnalyzesPayments(elasticData,doctorId) {
            let analyzes = _.filter(elasticData, {'service_payment_destination': "Аналізи",'doctor_id': doctorId});
            let totalSum = 0
            for (let item of analyzes) {
                totalSum += item.payed_amount
            }

            return totalSum
        },
        getSonarsPayments(elasticData,doctorId) {
            let sonars = _.filter(elasticData, {'service_payment_destination': "УЗД",'doctor_id': doctorId});
            let totalSum = 0
            for (let item of sonars) {
                totalSum += item.payed_amount
            }

            return totalSum
        },
        getTreatmentsPayments(elasticData,doctorId) {
            let treatments = _.filter(elasticData, {'service_payment_destination': "Лікування",'doctor_id': doctorId});
            let totalSum = 0
            for (let item of treatments) {
                totalSum += item.payed_amount
            }

            return totalSum
        },
        getTotalXrayDiagnosticsPayments(elasticData,doctorId) {
            let xRay = _.filter(elasticData, {'service_payment_destination': "Рентген",'doctor_id': doctorId});
            let diagnostics = _.filter(elasticData, {'service_payment_destination': "Діагностика",'doctor_id': doctorId});

            let totalSum = 0
            for (let item of diagnostics) {
                totalSum += item.payed_amount
            }
            for (let item of xRay) {
                totalSum += item.payed_amount
            }

            return totalSum
        },
        getDoctors(elasticData) {
            let doctors = []
            for (let item of elasticData){
                let doctor = {}
                doctor.id = item.doctor_id;
                doctor.name = item.doctor_name;
                doctors.push(doctor)
            }

            return doctors = Array.from(new Set(doctors.map(JSON.stringify))).map(JSON.parse);
        },
        fetchIncomePayments(filters) {
            return this.elasticSearchClient.getHits(this.incomePaymentsIndex, {
                size: CONSTANTS.ELASTICSEARCH.HITS_SIZE,
                query: {
                    bool: {
                        filter: [
                            {
                                bool: {
                                    should: [
                                        {
                                            range: {
                                                created_at: {
                                                    gte: filters.date_start,
                                                    lte: filters.date_end
                                                },
                                            }
                                        },
                                    ]
                                }
                            },
                            ...(_.isArray(filters.specialization) ? [{
                                terms: {
                                    appointment_card_specialization_id: filters.specialization
                                }
                            }] : [{
                                term: {
                                    appointment_card_specialization_id: filters.specialization
                                }
                            }]),
                            {
                                terms: {
                                    service_payment_destination_id: filters.payment_destination
                                }
                            }
                        ],
                    }
                }
            }, true)
                .then(response => {
                    return Promise.resolve(response);
                }).catch(e => {
                    console.error(e);
                    this.$error();
                });
        },
    }
}
