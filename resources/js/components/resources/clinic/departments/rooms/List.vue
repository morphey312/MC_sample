<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded">
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import Department from '@/models/department';

export default {
    props: {
        department: Object,
    },
    data() {
        return {
            model: new Department({id: this.department.id}),
            repository: new ProxyRepository(() => {
                return this.getRooms();
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название'),
                },
                {
                    name: 'status',
                    title: __('Активна'),
                    formatter: (value) => {
                        return this.$formatter.boolToString(value, '<span class="check-yes" />');
                    }
                },
            ],
        };
    },
    methods: {
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        loaded() {
            this.$emit('loaded');
        },
        getRooms() {
            return this.model.fetch(['rooms']).then(() => {
                return {
                    rows: this.model.rooms,
                };
            });
        },
    }
}
</script>