<template>
    <section class="contracts-container">
        <contracts-list 
            ref="table"
            :msp="msp"
            @selection-changed="setActiveItem"
            @loaded="refreshed" />
        <div class="mt-10">
            <el-button
                v-if="$can('msp-contracts.create')"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$can('msp-contracts.update')"
                :disabled="activeItem === null || !isEditable(activeItem)"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$can('msp-contracts.delete') && !wasSent(activeItem)"
                :disabled="activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
            <el-button
                v-if="$can('msp-contracts.delete') && wasSent(activeItem)"
                :disabled="activeItem === null || !isTerminable(activeItem)"
                @click="remove">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                v-if="$can('msp-contracts.update')"
                :disabled="!wasSent(activeItem)"
                @click="details">
                {{ __('Статус') }}
            </el-button>
        </div>
        <slot name="buttons"></slot>
    </section>
</template>

<script>
import ContractsList from './contracts/List.vue';
import FormCreate from './contracts/FormCreate.vue';
import FormEdit from './contracts/FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import ShowContract from './contracts/Details.vue';
import CONSTANT from '@/constants';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        ContractsList,
    },
    props: {
        msp: Object,
    },
    methods: {
        wasSent(contract) {
            return contract !== null && _.isFilled(contract.ehealth_request_id);
        },
        isTerminable(contract) {
            return contract.ehealth_status === CONSTANT.EHEALTH.CONTRACT_STATUS.NEW
                || contract.ehealth_status === CONSTANT.EHEALTH.CONTRACT_STATUS.APPROVED;
        },
        isEditable(contract) {
            return contract.ehealth_status !== CONSTANT.EHEALTH.CONTRACT_STATUS.TERMINATED;
        },
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    msp: this.msp,
                },
                editForm: FormEdit,
                editProps: {
                    msp: this.msp,
                },
                createHeader: __('Создать договор'),
                editHeader: __('Обновить договор'),
                width: '770px',
                modal: this.modalComponent,
                backText: __('Вернуться к списку договоров'),
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот договор?'),
                deleted: __('Договор был успешно удален'),
                created: __('Договор был успешно создан'),
                updated: __('Договор был успешно обновлен'),
            };
        },
        details() {
            this.modalComponent.pushComponent(ShowContract, {
                contract: this.activeItem,
            }, {
                close: (dialog) => {
                    dialog.popComponent();
                }
            }, {
                header: __('Договор'),
                width: '770px',
                backText: __('Вернуться к списку договоров'),
            });
        }
    },
}
</script>