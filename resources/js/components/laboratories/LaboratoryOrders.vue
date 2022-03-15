<template>
    <page
        type="flex"
        :title="__('Заказ-наряды в лабораторию')" >
        <el-tabs v-model="activeTab" class="tab-group-beige shrinkable-tabs">
            <el-tab-pane
                v-if="$canAccess('laboratory-orders')"
                :lazy="true"
                :label="__('Подготовлены к отправке')"
                name="orders">
                <section class="shrinkable pt-0">
                    <laboratory-orders
                        @show-details="showDetails" />
                </section>
            </el-tab-pane>
            <el-tab-pane
                v-if="$canAccess('laboratory-transfers')"
                :lazy="true"
                :label="__('Отправлены в лабораторию')"
                name="transfers">
                <section class="shrinkable pt-0">
                    <laboratory-transfers
                        @show-details="showDetails"/>
                </section>
            </el-tab-pane>
            <!--
            <el-tab-pane
                v-if="$canAccess('laboratory-orders')"
                :lazy="true"
                :label="__('Отложенные анализы')"
                name="delayed-analyses">
                <section class="shrinkable pt-0">
                    <delayed-analyses />
                </section>
            </el-tab-pane>
            -->
        </el-tabs>
    </page>
</template>
<script>
import LaboratoryOrders from './analyses/orders/Orders.vue';
import LaboratoryTransfers from './analyses/orders/Transfers.vue';
import DelayedAnalyses from './analyses/orders/DelayedAnalyses.vue';
import Details from './analyses/orders/Details.vue';

export default {
    components: {
        LaboratoryOrders,
        LaboratoryTransfers,
        DelayedAnalyses,
    },
    data() {
        return {
            activeTab: 'orders',
        }
    },
     methods: {
        showDetails(item, isTransfer) {
            this.$modalComponent(Details, {
                isTransfer: isTransfer,
                item: item,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: isTransfer ? __('Отправка № {number}', {number: item.barcode}) : __('Заказ № {number}', {number: item.number}),
                width: '920px',
            });
        },
    }
}
</script>
