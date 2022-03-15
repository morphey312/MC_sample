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

export default {
    props: {
        room: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return this.getPlaces();
            }),
            fields: [
                {
                    name: 'number',
                    title: __('Номер'),
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
    mounted() {
        this.$watch('room.places', () => {
            this.$refs.table.refresh();
        }, { deep: true });
    },
    methods: {
        selectionChanged(selection) {
            let index = this.room.places.findIndex(place => {
                return place.number === selection[0].number
            });
            this.$emit('selection-changed', {selection, index});
        },
        loaded() {
            this.$emit('loaded');
        },
        getPlaces() {
            return Promise.resolve({
                rows: this.room.places,
            });
        },
    }
}
</script>