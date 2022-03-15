import PriceHistory from '@/components/price-list/History.vue';
import CONSTANT from '@/constants';

export default {
    methods: {
        loadDuplicates() {
            this.loading = true;
            this.repository.fetchDuplicated({
                disabled: 0,
            }, [
                'default', 
                {name: 'prices', params: {
                    active: 1,
                    set_type: CONSTANT.PRICE.SET_TYPE.BASE,
                }},
            ], this.page, this.chunkSize).then((result) => {
                this.groups = this.createGroups(result);
                this.loading = false;
            });
        },
        pickMainItem(group) {
            return _.maxBy(group, i => i.prices.length).id;
        },
        listPrices(item) {
            return item.prices.map((price) => {
                return [
                    this.$formatter.numberFormat(price.cost),
                    this.$formatter.listFormat(price.clinic_names),
                ].join(' - ');
            });
        },
        showHistory(item, serviceType) {
            this.$modalComponent(PriceHistory, {
                serviceId: item.id,
                serviceType,
                setType: CONSTANT.PRICE.SET_TYPE.BASE,
                clinics: item.clinics.map(c => c.clinic_id),
            }, {}, {
                header: __('История тарифа'),
                width: '900px',
                customClass: 'no-footer',
            });
        },
    },
};