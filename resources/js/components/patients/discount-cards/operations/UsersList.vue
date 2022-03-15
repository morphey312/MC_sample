<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :filters="filters"
        :selectable-rows="true"
        table-height="110px"
        @header-filter-updated="syncFilters"
        @selection-changed="selectionChanged"
        @loaded="loaded">
        <div slot="footer-top" class="mt-10 el-col-24">
            <div class="el-col-12">{{ __('Всего пользователей картой:')}}
                <span>{{ card.users_count }}</span>
            </div>
            <div class="el-col-12 text-right">{{ __('Всего активных пользователей картой:')}}
                <span>{{ card.active_users_count}}</span>
            </div>
        </div>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    props: {
        users: {
            type: Array,
            default: () => [],
        },
        card: Object
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.getSortedUsers(),
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: this.tableTitle(),
                    dataClass: 'no-dash',
                    width: '70%',
                },
                {
                    name: 'is_owner',
                    title: __('Владелец карты'),
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    width: '15%',
                },
                {
                    name: 'disabled',
                    title: __('Не активна'),
                    dataClass: 'no-dash',
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    },
                    width: '15%',
                },
            ],
        };
    },
    methods: {
        syncFilters(updates) {
            this.$emit('header-filter-updated', updates);
        },
        selectionChanged(selection) {
            this.$emit('patient-selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        addRowClass(item, index) {
            return item.call_delete_reason_id ? 'deleted-row' : '';
        },
        getSortedUsers() {
            return _.orderBy(this.users, 'is_owner','desc');
        },
        tableTitle() {
            return '<span class="float-left">'+ __('Контактное лицо (Всего: {users})', {users: this.users.length}) + '</span>' +
                '<span class="float-right">' + __('Срок действия карты: {expire}', { expire: this.$formatter.dateFormat(this.card.expires, 'DD/MM/YYYY')}) + '</span>';
        }
    },
};
</script>
