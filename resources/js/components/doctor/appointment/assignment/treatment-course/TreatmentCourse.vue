<template>
    <div
        v-if="hasServices"
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
                <template v-if="!props.rowData.payed || props.rowData.payed == 0">
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
import TreatmentServiceMixin from './mixin/treatment-service';

export default {
    mixins: [
        TreatmentServiceMixin,
    ],
    props: {
        services: {
            type: Array,
            default: () => [],
        },
        title: {
            type: [Boolean, String],
            default: __('Назначено лечение'),
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
                    rows: this.serviceList,
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название лечения'),
                },
                {
                    name: 'specialization.name',
                    title: __('Специализация'),
                    width: "130px",
                },
                ...(this.readonly ? [] : [{
                    name: 'remove',
                    title: '',
                    width: "30px",
                    dataClass: 'no-ellipsis',
                }]),
            ],
            serviceList: this.getServices(),
        }
    },
    computed: {
        hasServices() {
            return this.serviceList.length != 0;
        },
        total(){
            return this.serviceList.reduce((total, row) => {
                return total + (Number(row.cost));
            }, 0);
        }
    },
    watch: {
        ['services']() {
            this.serviceList = this.getServices();
            this.$nextTick(() => {
                if (this.$refs.table) {
                    this.$refs.table.refresh();
                }
            })
        },
    },
    methods: {
        getServices() {
            if (this.services.length === 0) {
                return [];
            }
            return this.services.filter((service) => service.is_base == true);
        },
        remove(service) {
            if (service.payed != undefined && service.payed > 0) {
                return this.$error(__('Нельзя удалить, есть оплата'));
            }
            this.$emit('remove-treatment', service);
        }
    }
}
</script>
