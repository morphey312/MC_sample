<template>
    <order-list
        ref="table"
        @loaded="refreshed"
        @selection-changed="setActiveItem"
        @show-details="showDetails">
        <div class="buttons" slot="buttons">
            <el-button
                type="primary"
                v-if="$canCreate('laboratory-transfers')"
                ref="createTransferButton"
                @click="createTransfer" >
                {{ __('Сформировать отправку') }}
            </el-button>
        </div>
    </order-list>
</template>
<script>
import OrderList from './OrderList.vue';
import ManageMixin from '@/mixins/manage';
import PrepareTransfer from './PrepareTransfer.vue';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        OrderList,
    },
    data() {
        return {
            selectedTo: [],
        }
    },
    methods: {
        showDetails(item) {
            this.$emit('show-details', item, false);
        },
        createTransfer() {
            this.$refs.createTransferButton.$el.blur()
            this.$modalComponent(PrepareTransfer, {
            }, {
                close: (dialog) => {
                    dialog.close();
                },
                confirm: (dialog, orders) => {
                    orders.save().then(() => {
                        this.$info(__('Заказ-наряд успешно создан'));
                        dialog.close();
                        this.refresh();
                    }).catch((e) => {
                        this.loading = false;
                        this.$displayErrors(e);
                        this.$error(__('Ошибка при сохранении'));
                    });

                },
            }, {
                header: __('Сформировать отправку в лабораторию (трансферная ведомость)'),
                width: '1200px',
                customClass: 'no-footer',
            });
        },
        setActiveItem(selection) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
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
