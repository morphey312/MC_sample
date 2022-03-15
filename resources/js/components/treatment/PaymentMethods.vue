<template>
    <page
        :title="__('Виды оплат')"
        type="flex">
        <section class="shrinkable">
            <payment-method-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('payment-methods.create')"
                        @click="create" >
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$can('payment-methods.update')"
                        :disabled="activeItem === null"
                        @click="edit" >
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$can('payment-methods.delete')"
                        :disabled="activeItem === null"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                    <form-button
                        v-if="$can('specializations.access')"
                        :disabled="activeItem === null"
                        :text="__('Операции')"
                        @click="showLog" />
                </div>
            </payment-method-list>
        </section>
    </page>
</template>

<script>
import PaymentMethodList from './payment-methods/List.vue';
import FormCreate from './payment-methods/FormCreate.vue';
import FormEdit from './payment-methods/FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import PaymentMethod from '@/components/action-log/PaymentMethod.vue';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        PaymentMethodList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить вид оплаты'),
                editHeader: __('Редактировать вид оплаты'),
                width: '700px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот вид оплаты?'),
                deleted: __('Вид оплаты успешно удален'),
                created: __('Вид оплаты успешно добавлен'),
                updated: __('Вид оплаты успешно обновлен'),
            };
        },
        showLog() {
            this.$modalComponent(PaymentMethod, {
                id: this.activeItem.id,
            }, {
                close: (dialog) => {
                    dialog.close();
                },
            }, {
                header: __('История изменения методов оплаты'),
                width: '900px',
                customClass: 'no-footer',
            });
        }
    }
}
</script>
