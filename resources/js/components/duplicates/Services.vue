<template>
    <page
        :title="__('Дубликаты услуг')"
        type="flex">
        <section
            v-loading="loading"
            class="grey-cap shrinkable">
            <groups-table
                :groups="groups"
                :page="page"
                :chunk-size="chunkSize"
                :total-text="totalText"
                :columns="columns"
                @load-prev="loadPrev"
                @load-next="loadNext"
                @merge="mergeServices">
                <template slot="data" slot-scope="props">
                    <td class="no-ellipsis">
                        <div class="has-icon">
                            <span class="flex-expand text-select">
                                {{ props.item.name }}
                            </span>
                            <svg-icon
                                name="info-alt"
                                class="icon-tiny icon-grey"
                                @click.stop="showPriceHistory(props.item)" />
                        </div>
                    </td>
                    <td>{{ props.item.specialization_name }}</td>
                    <td>{{ props.item.payment_destination }}</td>
                    <td class="no-ellipsis">{{ $formatter.listFormat(listPrices(props.item)) }}</td>
                </template>
            </groups-table>
        </section>
    </page>
</template>


<script>
import ServiceRepository from '@/repositories/service';
import CONSTANT from '@/constants';
import DuplicatesMixin from './mixins/duplicates';
import ServicesMixin from './mixins/services';

export default {
    mixins: [
        DuplicatesMixin,
        ServicesMixin,
    ],
    data() {
        return {
            repository: new ServiceRepository(),
            columns: [
                {title: __('Название услуги')},
                {title: __('Специализация'), width: '15%'},
                {title: __('Назначение платежа'), width: '15%'},
                {title: __('Активные тарифы'), width: '35%'},
            ],
        };
    },
    computed: {
        totalText() {
            return __('Найдено {count} групп схожих услуг', {count: this.groups.length});
        },
    },
    mounted() {
        this.loadDuplicates();
    },
    methods: {
        showPriceHistory(item) {
            this.showHistory(item, CONSTANT.PRICE.SERVICE_TYPE.SERVICES);
        },
        mergeServices() {
            this.merge().then((ok) => {
                if (ok) {
                    this.$info(__('Услуги успешно объединены'));
                }
            });
        },
    },
};
</script>
