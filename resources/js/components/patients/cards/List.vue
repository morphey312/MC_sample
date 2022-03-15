<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @loaded="loaded"
        @selection-changed="selectionChanged" />
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        patient: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return  Promise.resolve({
                            rows: this.getCardList(),
                        });
            }),
            fields: [
                {
                    name: 'number',
                    title: __('Номер'),
                },
                {
                    name: 'is_archive',
                    title: __('Архив'),
                    dataClass: 'no-dash',
                    formatter: (val) => {
                        return this.$formatter.boolToString(val, '<span class="check-yes" />');
                    },
                },
                {
                    name: 'specialization.name',
                    title: __('Специализация'),
                },
            ],
        }
    },
    mounted() {
        this.$watch('patient.cards', () => {
            this.$refs.table.refresh();
        }, { deep: true });
    },
    methods: {
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
        getCardList() {
            return [
                ...this.patient.getCardsWithSpecializations(),
                ...this.getArchiveList(),
            ];
        },
        getArchiveList() {
            let card = this.patient.cards.length !== 0 ? this.patient.cards[0] : null;
            
            if (card && card.archive_numbers) {
                return card.archive_numbers.map(number => {
                   number.is_archive = true;
                   return number;
                });
            }
            
            return [];
        },
    },
}
</script>