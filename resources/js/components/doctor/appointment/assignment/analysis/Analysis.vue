<template>
    <div
        v-if="analyses.length !== 0"
        class="paragraph">
        <h3 v-if="title">
            {{ title }}
            <template v-if="!readonly">
                / <a href="#" @click.prevent="$emit('edit')">{{ __('Редактировать') }}</a>
            </template>
        </h3>
        <manage-table
            ref="table"
            :fields="fields"
            :repository="repository"
            table-height="auto">
            <template
                slot="remove"
                slot-scope="props" >
                <template v-if="isDeleteable(props.rowData)">
                    <span class="" @click="remove(props.rowIndex)">
                        <svg-icon name="delete" class="icon-small icon-blue" />
                    </span>
                </template>
            </template>
        </manage-table>
        <div class="block-total">{{ __('На сумму:') }} <span>{{ $formatter.numberFormat(total) }}</span></div>
    </div>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import CONSTANTS from '@/constants';

export default {
    props: {
        analyses: Array,
        title: {
            type: [Boolean, String],
            default: __('Назначены анализы'),
        },
        readonly: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.analyses,
                });
            }),
            fields: [
                {
                    name: 'created',
                    title: __('Дата назначения'),
                    width: "90px",
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'analysis.laboratory_name',
                    title: __('Название лаборатории'),
                    width: "110px",
                },
                {
                    name: 'status',
                    title: __('Статус'),
                    width: '100px',
                    formatter: (value) => {
                        return this.$handbook.getOption('analysis_status', value);
                    },
                },
                {
                    name: 'analysis.name',
                    title: __('Название анализов'),
                },
                {
                    name: 'quantity',
                    title: __('Кол-во анализов'),
                    width: "70px",
                    dataClass: 'text-right',
                },
                {
                    name: 'date_expected_pass',
                    title: __('Реком. Дата сдачи'),
                    width: "100px",
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                    dataClass: 'text-right',
                },
                ...(this.readonly ? [] : [{
                    name: 'remove',
                    title: '',
                    width: "30px",
                    dataClass: 'no-ellipsis no-dash',
                }]),
            ],
        }
    },
    computed: {
        total(){
            return this.analyses.reduce((total, row) => {
                return total + (Number(row.cost));
            }, 0);
        }
    },
    watch: {
        ['analyses']() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
    methods: {
        isDeleteable(row) {
            if (_.isVoid(row.appointment_id)) {
                return row.status === CONSTANTS.ANALYSIS_RESULT.STATUSES.ASSIGNED || this.readonly;
            }
            return false;
        },
        remove(index) {
            this.$emit('remove-analysis', index);
        }
    }
}
</script>
