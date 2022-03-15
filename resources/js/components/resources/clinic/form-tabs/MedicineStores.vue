<template>
    <div>
        <form-select
            ref="country"
            :entity="model"
            property="medicine_firm_id"
            :options="medicineFirms"
            :label="__('Название фирмы из 1С')" />
        <transfer-table
            ref="transfer"
            :items="stores"
            v-model="model.medicine_stores"
            :left-title="__('Склад')"
            left-width="310px"
            :right-title="__('Склад')"
            right-width="310px"
            :emptySelectionMessage="placeholder">
        </transfer-table>
    </div>
</template>

<script>
import MedicineStoreRepository from '@/repositories/medicine-store';
import MedicineFirmRepository from "@/repositories/medicine-firm";

export default {
    props: {
        model: Object,
    },
    data() {
        return {
            stores: [],
            medicineFirms: [],
            placeholder: this.getPlaceholder(),
        }
    },
    mounted() {
        this.getStores();
        this.getMedicineFirms();
    },
    methods: {
        getStores() {
            let store = new MedicineStoreRepository();
            store.fetchList().then((response) => {
                this.stores = response;
            });
        },
        getMedicineFirms() {
            let medicineFirm = new MedicineFirmRepository();
            medicineFirm.fetchList().then((response) => {
                this.medicineFirms = response;
            });
        },
        getPlaceholder() {
            return this.$formatter.makePlaceholderImage('content/transfer-no-data.svg', __('Выберите и добавьте склады слева'));
        },
    },
}
</script>
