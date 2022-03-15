<template>
    <page
        :title="__('Дубликаты анализов')"
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
                @merge="mergeAnalyses">
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
                    <td>{{ props.item.laboratory_name }}</td>
                    <td class="text-select">{{ props.item.laboratory_code }}</td>
                    <td class="no-ellipsis">{{ $formatter.listFormat(listPrices(props.item)) }}</td>
                </template>
            </groups-table>
        </section>
    </page>
</template>

<script>
import AnalysisRepository from '@/repositories/analysis';
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
            repository: new AnalysisRepository(),
            columns: [
                {title: __('Название анализа')},
                {title: __('Лаборатория'), width: '10%'},
                {title: __('Код лаборатории'), width: '10%'},
                {title: __('Активные тарифы'), width: '35%'},
            ],
        };
    },
    computed: {
        totalText() {
            return __('Найдено {count} групп схожих анализов', {count: this.groups.length});
        },
    },
    mounted() {
        this.loadDuplicates();
    },
    methods: {
        showPriceHistory(item) {
            this.showHistory(item, CONSTANT.PRICE.SERVICE_TYPE.ANALYSIS);
        },
        mergeAnalyses() {
            this.merge().then((ok) => {
                if (ok) {
                    this.$info(__('Анализы успешно объединены'));
                }
            });
        },
    },
};
</script>