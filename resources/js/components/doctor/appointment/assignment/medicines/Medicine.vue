<template>
    <div
        v-if="medicines.length !== 0"
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
            <template slot="is_apteka24" slot-scope="props">
                <svg-icon
                    v-show="props.rowData.is_apteka24"
                    name="apteka24"
                    class="icon-small icon-blue"
                ></svg-icon>
            </template>
        </manage-table>
        <div class="block-total">{{ __('На сумму:') }} <span>{{ $formatter.numberFormat(total) }}</span></div>
    </div>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        medicines: Array,
        title: {
            type: [Boolean, String],
            default: __('Назначены медикаменты'),
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
                    rows: this.medicines,
                });
            }),
            fields: [
                {
                    name: 'is_apteka24',
                    title: '',
                    width: '30px',
                    dataClass: 'text-center',
                },
                {
                    name: 'name',
                    title: __('Название медикамента'),
                },
                {
                    name: 'medication_duration',
                    title: __('Принимать, дней'),
                    width: "80px",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'quantity',
                    title: __('Кол-во, шт'),
                    width: "70px",
                    titleClass: 'text-right',
                    dataClass: 'text-right',
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                    width: "200px",
                    dataClass: 'no-dash',
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
            return this.medicines.reduce((total, row) => {
                return total + (Number(row.cost));
            }, 0);
        }
    },
    watch: {
        ['medicines']() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
    methods: {
        remove(index) {
            this.$emit('remove-medicine', index);
        },
        edit() {
            this.$emit('edit-medicine');
        },
    }
}
</script>
