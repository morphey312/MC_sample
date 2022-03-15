<template>
    <div class="sections-wrapper">
        <section class="flex-content">
            <destinations-list 
                ref="table"
                :repository="repository"
                @selection-changed="setActiveItem"
                @loaded="refreshed" >
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('discount-card-types.update')"
                        @click="create">
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$can('discount-card-types.update')"
                        :disabled="activeItem === null"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$can('discount-card-types.update')"
                        :disabled="activeItem === null"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </destinations-list>
        </section>
    </div>
</template>
<script>
import PaymentDestinationRepository from '@/repositories/service/payment-destination';
import ProxyRepository from '@/repositories/proxy-repository';
import PaymentDestination from '@/models/discount-card-type/payment-destination';
import DestinationsList from './payment-destinations/List.vue';
import FormCreate from './payment-destinations/FormCreate.vue';
import FormEdit from './payment-destinations/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        DestinationsList,
    },
    props: {
        model: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.model.payment_destinations,
                });
            }),
            destinations: [],
        }
    },
    mounted() {
        this.getPaymentDestinations();
    },
    methods: {
        getPaymentDestinations() {
            let destination = new PaymentDestinationRepository();
            destination.fetchList().then((response) => {
                this.destinations = response;
            });
        },
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    destinations: this.destinations,
                    discountType: this.model,
                },
                editForm: FormEdit,
                editProps: {
                    destinations: this.destinations,
                    item: this.activeItem,
                    discountType: this.model,
                },
                createHeader: __('Добавить скидку по назначению платежа'),
                editHeader: __('Изменить скидку по назначению платежа'),
                width: '500px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту скидку?'),
                deleted: __('Скидка по назначению платежа была успешно удалена'),
                created: __('Скидка по назначению была успешно добавлена'),
                updated: __('Скидка по назначению была успешно обновлена'),
            };
        },
        onCreated(discount) {
            this.model.payment_destinations.push(discount);
        },
        onUpdated(discount) {
            let index = this.getPaymentIndex();
            if (index !== -1) {
                this.model.payment_destinations.splice(index, 1, discount);
            }
        },
        remove() {
            let messages = {
                ...this.defaultMessages(),
                ...this.getMessages(),
            };
            this.$confirm(messages.deleteConfirmation, () => {
                this.removePayment().then((response) => {
                    if (messages.deleted) {
                        this.$info(messages.deleted);
                    }
                    this.lastActiveItemId = null;
                    this.activeItem = null;
                    this.refresh();
                });
            });
        },
        getPaymentIndex() {
            return this.model.payment_destinations.findIndex(item => item.id === this.activeItem.id);
        },
        removePayment() {
            let index = this.getPaymentIndex();
            if (index !== -1) {
                this.model.payment_destinations.splice(index, 1);    
            }
            return Promise.resolve();
        },
    },
}   
</script>