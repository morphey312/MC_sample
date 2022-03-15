import AssignedMedicine from '@/models/patient/assigned-medicine';
import ProxyRepository from '@/repositories/proxy-repository';
import Details from '../Details.vue';

export default {
    components: {
        Details,
    },
    props: {
        patient: Object,
        clinics: {
            type: Array,
            default: () => [],
        }
    },
    data() {
        return {
            repository: new ProxyRepository(({filters, scopes}) => {
                return this.getRows(filters, scopes);
            }),
            scopes: [
                'store_medicine',
                'assigner',
                'card_specialization',
                'patient',
                'clinic',
            ],
            changedItems: [],
            loading: false,
        }
    },
    methods: {
        getFilterUid() {
            return false;
        },
        loaded() {
            this.$emit('loaded');
        },
        getTable() {
            return this.$refs.table;
        },
        refresh() {
            this.getTable().refresh();
        },
        getData() {
            return this.getTable().getData();
        },
        castMedicine(row) {
            let storeRests = this.getUserStores(row.store_rests);
            return {
                id: row.id,
                medicine_id: row.medicine_id,
                clinic_id: row.clinic_id,
                clinic: row.clinic,
                quantity: row.quantity,
                issued: row.issued,
                to_issue: this.getToIssue(row),
                created: row.created,
                card_number: row.card_number,
                card_specialization_id: row.card_specialization_id,
                card_specialization_name: row.card_specialization_name,
                assigner: row.assigner,
                name: row.name,
                is_free: row.is_free,
                self_cost: this.$formatter.numberFormat(row.self_cost),
                base_cost: this.$formatter.numberFormat(row.base_cost),
                cost: this.$formatter.numberFormat(row.cost),
                store_rests: storeRests,
                rests_total: this.getRestsTotal(storeRests),
                issue_quantity: 0,
                to_pay: 0,
                by_policy: row.by_policy,
                franchise: row.franchise,
                warranter: row.warranter,
            }
        },
        getUserStores(stores) {
            return stores.filter(store => {
                let hasStore = false;
                this.clinics.forEach(clinic_id => {
                    if (store.clinics.indexOf(clinic_id) !== -1) {
                        hasStore = true;
                    }
                });
                return hasStore;
            })
        },
        getRestsTotal(row) {
            return row.reduce((total, store) => (total + Number(store.rest)), 0);
        },
        getToIssue(row) {
            return Number((row.quantity - row.issued_quantity).toFixed(3));
        },
        maxToIssue(row) {
            return (row.to_issue > row.rests_total) ? row.rests_total : row.to_issue;
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
        showDetails(medicine) {
            this.$modalComponent(Details, {
                medicine,
            },
            {},
            {
                header: medicine.name,
                width: '600px',
                customClass: 'no-footer',
            });
        },
        updateChangedItems(row) {
            let index = this.changedItems.findIndex(item => item.id == row.id);
            if (index == -1) {
                this.changedItems.push(row);
            } else {
                if (row.issue_quantity == 0) {
                    this.changedItems.splice(index, 1);
                }
            }
            this.$emit('selection-changed', this.changedItems);
        },
        deleteAssigned(row) {
            this.loading = true;
            let medicine = new AssignedMedicine({id: row.id});
            return medicine.delete().then((response) => {
                this.$info(__('Медикамент удален'));
                this.loading = false;
                this.refresh();
                return Promise.resolve();
            }).catch((error) => {
                this.$info(__('Не удалось удалить медикамент'));
                return Promise.reject();
            });
        },
    },
}
