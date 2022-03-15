<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        @loaded="loaded">
        <template 
            slot="ehealth_status"
            slot-scope="props">
            <div class="has-icon">
                <span class="ellipsis">
                    {{ $handbook.getOption('ehealth_contract_status', props.rowData.ehealth_status) }}
                </span>
                <el-popover
                    v-if="props.rowData.ehealth_status_reason"
                    :title="$handbook.getOption('ehealth_contract_status', props.rowData.ehealth_status)"
                    width="300"
                    trigger="hover"
                    :content="props.rowData.ehealth_status_reason">
                    <svg-icon 
                        slot="reference"
                        name="info-alt" 
                        class="icon-tiny icon-grey" />
                </el-popover>
            </div>
        </template>
    </manage-table>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import Msp from '@/models/msp';

export default {
    props: {
        msp: Object,
    },
    data() {
        return {
            model: new Msp({id: this.msp.id}),
            repository: new ProxyRepository(() => {
                return this.getContracts();
            }),
            fields: [
                {
                    name: 'type',
                    title: __('Тип'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$handbook.getOption('msp_contract_type', value);
                    },
                },
                {
                    name: 'contract_number',
                    title: __('Номер'),
                },
                {
                    name: 'start_date',
                    title: __('Дата вступления в силу'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'end_date',
                    title: __('Дата окончания действия'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'ehealth_status',
                    title: __('Статус'),
                    width: '20%',
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
        getContracts() {
            return this.model.fetch(['contracts']).then(() => {
                return {
                    rows: this.model.contracts,
                };
            });
        },
    }
}
</script>