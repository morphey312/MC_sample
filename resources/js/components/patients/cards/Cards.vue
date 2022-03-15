<template>
    <page
        :title="__('Медицинские карты')"
        type="table">
        <section class="grey-cap">
            <cards-list
                v-if="patient !== null"
                ref="table"
                :patient="patient"
                @selection-changed="setActiveItem"
                @loaded="refreshed" />
        </section>
        <div class="manage-table-buttons">
            <el-button
                v-if="$can('card-specializations.create')"
                :disabled="patient === null || patient.loading"
                type="primary"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$can('card-specializations.delete')"
                type="primary"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </page>
</template>

<script>
import CardsList from './List.vue';
import CreateCard from './Create.vue';
import Patient from '@/models/patient';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CardsList,
    },
    data() {
        return {
            patient: null,
        };
    },
    mounted() {
        this.patient = new Patient({id: this.$route.params.id});
    },
    methods: {
        getModalOptions() {
            return {
                createForm: CreateCard,
                createProps: {
                    patient: this.patient,
                },
                createHeader: __('Добавить карту'),
                width: '500px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту карту?'),
                deleted: __('Карта была успешно удалена'),
                created: __('Карта была успешно добавлена'),
            };
        },
    },
};
</script>