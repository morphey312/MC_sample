<template>
    <page
        :title="__('Назначение платежа')"
        type="flex">
        <section class="grey-cap shrinkable">
            <destination-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed" >
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('service-payment-destinations.create')"
                        @click="create">
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$can('service-payment-destinations.update')"
                        :disabled="activeItem === null"
                        @click="edit">
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$can('service-payment-destinations.delete')"
                        :disabled="activeItem === null"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </destination-list>
        </section>
    </page>
</template>

<script>
import DestinationList from './payment-destination/List.vue';
import CreateAnalysis from './payment-destination/FormCreate.vue';
import EditAnalysis from './payment-destination/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin
    ],
    components: {
        DestinationList,
    },
    methods: {
        getModalOptions() {
            return {
                createForm: CreateAnalysis,
                editForm: EditAnalysis,
                createHeader: __('Добавить назначение платежа'),
                editHeader: __('Изменить назначение платежа'),
                width: '500px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить это назначение платежа?'),
                deleted: __('Назначение платежа было успешно удалено'),
                created: __('Назначение платежа было успешно добавлено'),
                updated: __('Назначение платежа было успешно обновлено'),
            };
        },
    },
};
</script>