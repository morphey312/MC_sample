<template>
    <div
        v-if="analyses.length !== 0"
        class="paragraph">
        <h3 v-if="title">
            {{ title }}
            <template v-if="!readonly">
                <a href="#" @click.prevent="$emit('edit')">{{ __('Редактировать') }}</a>
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
                    <span class="" @click="remove(props.rowData)">
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

export default {
    props: {
        appointment: Object,
        title: {
            type: [Boolean, String],
            default: __('Произведен забор анализов в рамках приема.'),
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
                    name: 'analysis.name',
                    title: __('Название анализов'),
                },
                {
                    name: 'analysis.laboratory_name',
                    title: __('Название лаборатории'),
                    width: "110px",
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
        },
        analyses() {
            return this.appointment.analyses_results;
        },
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
            if (row.payed > 0) {
                return false;
            } else if (!_.isFilled(row.date_pass)) {
                return true;
            } else if (_.isVoid(row.date_pass)) {
                return true;
            }
        },
        remove(index) {
            this.$emit('remove-doctor-analysis', index);
        }
    }
}
</script>
