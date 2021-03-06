<template>
    <div
        v-if="procedures.length !== 0"
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
                <span class="" @click="remove(props.rowIndex)">
                    <svg-icon name="delete" class="icon-small icon-blue" />
                </span>
            </template>
        </manage-table>
        <div class="block-total">{{ __('На сумму:') }} <span>{{ $formatter.numberFormat(total) }}</span></div>
    </div>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        procedures: Array,
        title: {
            type: [Boolean, String],
            default: __('Назначены процедуры'),
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
                    rows: this.procedures,
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название процедуры'),
                },
                {
                    name: 'assigned_quantity',
                    title: __('Количество процедур'),
                    width: "129px",
                    dataClass: 'text-right',
                },
                ...(this.readonly ? [] : [{
                    name: 'remove',
                    title: '',
                    width: "30px",
                    dataClass: 'no-ellipsis',
                }]),
            ],
        }
    },
    computed: {
        total(){
            return this.procedures.reduce((total, row) => {
                return total + (Number(row.cost));
            }, 0);
        }
    },
    watch: {
        ['procedures']() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
    methods: {
        remove(index) {
            this.$emit('remove-procedure', index);
        }
    }
}
</script>
