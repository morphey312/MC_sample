import Service from '@/models/appointment/service';
import AssignedService from '@/models/patient/assigned-service';
import CONSTANTS from '@/constants';
import PriceCalculationMixin from '@/mixins/appointment/analysis/price-calculation';

export default {
    mixins: [
        PriceCalculationMixin
    ],
    props: {
        appointment: Object,
        insurancePolicy: Object,
        readonly: Boolean,
        costInitial: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            activeTab: 'ordinary',
            loading: true,
            costType: this.costInitial,
            featuredList: [],
        };
    },
    mounted() {
        this.getFeaturedServices();
    },
    methods: {
        getFilterValues(override = {}) {
            return {
                hasPrice: {
                    clinic: this.appointment.clinic_id,
                    from: this.appointment.date,
                    to: this.appointment.date,
                    set: [CONSTANTS.PRICE.SET_TYPE.BASE],
                },
                disabled: false,
                ...override,
            };
        },
        toggleCost(val) {
            this.costType = val;
        },
        getFeaturedServices() {
            let params = {};
            if (this.insurancePolicy) {
                params.withInsurer = this.insurancePolicy.insurance_company_id;
            }
            this.employee.fetchFeaturedServices(this.getFeaturedFilter(), [], params).then((response) => {
                this.featuredList = response.map(row => this.createServiceModel(row));
                this.loading = false;
            });
        },
        createServiceModel(item) {
            let service = new Service();
            service.castServiceDataToEntity(item, this.filters);
            return service;
        },
        toggleDoctorFeatured(service_id) {
            this.employee.saveFeaturedService(service_id);
        },
        toggleFeatured(service) {
            let featuredIndex = this.featuredList.findIndex((item) => {
                return item.service_id === service.service_id;
            });

            if (featuredIndex === -1) {
                this.featuredList.push(service);
            } else {
                this.featuredList.splice(featuredIndex, 1);
            }

            this.toggleDoctorFeatured(service.service_id);
        },
        addToSelected({row}) {
            let sameIndex = this.selectedRows.findIndex((item) => {
                return item.service_id == row.service_id &&
                        item.is_free != this.costType;
            });

            if (sameIndex !== -1) {
                let warning = __('{name} уже присутствует в выбранном списке. Увеличить количество?', {name: row.name});
                return this.$confirm(warning, () => {
                    let service = this.selectedRows[sameIndex];
                    service.quantity++;
                    this.calcModelPrice(service);
                });
            }

            let service = new AssignedService(row._attributes);
            service.is_free = !this.costType;
            this.calcModelPrice(service);
            let discount = this.calcModelDiscount(service, 'service');
            if (discount > service.discount) {
                service.discount = discount;
            }
            this.selectedRows.splice(0, 0, service);
        },
        calcModelPrice(service) {
            this.$nextTick(() => {
                if (this.clinicRoundedCost()) {
                    service.cost = (this.getPrice(service) * service.quantity).toFixed(2);
                } else {
                    service.cost = Math.round((this.getPrice(service) * service.quantity)).toFixed(2);
                }
            });
        },
        getPrice(service) {
            let price;

            if (_.isFilled(service.discount)) {
                let cost;
                if (service.is_free) {
                    cost = service.self_cost;
                } else {
                    cost =  service.price;
                }
                price  = cost - (cost / 100 * service.discount);
            } else {
                price = service.price;
            }
            return price;
        },
    },
}
