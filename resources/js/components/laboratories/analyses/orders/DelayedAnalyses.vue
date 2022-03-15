<template>
    <analysis-list
        ref="table"
        @loaded="refreshed"
        @selection-changed="setActiveItem">
        <div class="buttons" slot="buttons">
            <el-button
                type="primary"
                v-if="$canCreate('laboratory-transfers')"
                :disabled="selectedTo.length === 0"
                ref="biomaterialRegistrationButton"
                @click="biomaterialRegistration" >
                {{ __('Сформировать заказ') }}
            </el-button>
            <el-button
                v-if="$canCreate('laboratory-transfers')"
                :disabled="selectedTo.length === 0"
                @click="remove">
                {{ __('Утилизировань контейнер') }}
            </el-button>
        </div>
    </analysis-list>
</template>
<script>
import AnalysisList from './AnalysisList.vue';
import ManageMixin from '@/mixins/manage';
import BatchRequest from '@/services/batch-request';
import biomaterialRegistration from './biomaterial-registration/Form.vue';
import Item from '@/models/analysis/laboratory/order/item';
import Container from '@/models/analysis/laboratory/order/item/container';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        AnalysisList,
    },
    data() {
        return {
            selectedTo: [],
            batchRequest: new BatchRequest('api/v1/analysis/laboratories/orders/items/batch'),
            batchContainerRequest: new BatchRequest('api/v1/analysis/laboratories/orders/items/containers/batch'),
          
        }
    },
    methods: {
        biomaterialRegistration() {
            let analysis = this.getManageTable().getData().filter((row) => this.selectedTo.indexOf(row.id) !== -1);
            analysis =  _.uniqBy(analysis, 'result_id')
            this.$refs.biomaterialRegistrationButton.$el.blur();
            this.$modalComponent(biomaterialRegistration, {
                laboratoryAnalyses: analysis,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                save: (dialog) => {
                    dialog.close();
                    this.refresh()
                },
            }, {
                header: __('Регистрация био-материалов'),
                width: '1300px',
                customClass: 'padding-0',
            });
        },
        remove() {
            this.$confirm(__('Вы дейстельно хотите утилизировань контейнера?'), () => {
                this.removeConfirmedItems();
            })
          
        },
        removeConfirmedItems() {
            this.loading = true;
            this.batchContainerRequest.reset();
            this.selectedTo.forEach(item => {
                let container = new Container({id: item});
                this.batchContainerRequest.delete(container);
            })
            this.batchContainerRequest.submit().then((result) => {
                this.$info(__('Контейнера успешно удалены'));
                this.refresh();
            });
        },
        createOrder() {
            this.loading = true;
            this.batchRequest.reset();
           
            let repo = new OrderItemRepository({filters: {id: this.selectedTo}, scopes: ['result']});
            repo.fetch().then(res => {
                let items = _.groupBy(res.rows, 'appointment_id');
                Object.keys(items).forEach(key => {
                    let order = new Order({
                        appointment_id: items[key][0].appointment_id,
                        clinic_id: items[key][0].clinic_id,
                        items: [... items[key]]
                    });
                    this.batchRequest.create(order);
                })
                this.batchRequest.submit().then((result) => {
                    this.$info(__('Данные успешно обновлены'));
                    this.refresh();
                });
            })
        },
        setActiveItem(selection) {
            this.selectedTo = selection;
        },
        refresh() {
            this.getManageTable().refresh();
        },
        refreshed() {
            if (this.selectedTo.length !== 0) {
                this.getManageTable().updateSelection((item) => {
                    return this.selectedTo.indexOf(item.id) !== -1;
                });
                this.selectedTo = [];
            }
        },
    }
}
</script>
