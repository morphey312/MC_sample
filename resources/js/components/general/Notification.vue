<template>
    <div class="flex-content" style="overflow-y: auto">
        <manage-table
            ref="table"
            :fields="fields"
            :filters="filters"
            :initial-sort-order="initialSortOrder"
            :repository="repository"
            :flex-height="true">
            <template slot="data" slot-scope="props">
                <span v-if="props.rowData.data.text">
                    <a href="#" @click.prevent="emitAction(props.rowData.data)" v-if="props.rowData.data.text">
                        {{ props.rowData.data.text }}
                    </a>
                    <span v-else>
                        {{ props.rowData.data.text }}
                    </span>
                </span>
                <span v-else>
                      {{ props.rowData.data }}
                </span>
        </template>
            <template slot="footer-top">
                <slot name="buttons" />
            </template>
    </manage-table>
    </div>
</template>

<script>

import NotificationRepository from '@/repositories/notification';

export default {
    data() {
        return {
            repository: new NotificationRepository(),
            fields: [
                {
                    name: 'created_at',
                    title: __('Дата'),
                    width: '30%',
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                },
                {   
                    name: 'data',
                    title: __('Сообщение'),
                    width: '70%',
                },
            ],
            filters: {
                owner: this.$store.state.user.id
            },
            initialSortOrder: [
                {field: 'created', direction: 'desc'},
            ],
        }
    },
    mounted() {
        this.$store.commit('markAsReadedNotification', 0);
    },
    methods: {
        emitAction(data) {
            this.$eventHub.$emit(data.click_action, data.room_id);
        }
    }
}
</script>