<template>
    <page
        :title="__('Дубликаты пациентов')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey filter">
                <patient-filter
                    :initial-state="filters"
                    @changed="startSearch" />
            </section>
        </drawer>
        <section
            v-if="displayTable"
            v-loading="loading"
            class="grey-cap shrinkable">
            <groups-table
                :groups="groups"
                :page="page"
                :chunk-size="chunkSize"
                :total-text="totalText"
                :columns="columns"
                table-width="150%"
                @load-prev="loadPrev"
                @load-next="loadNext"
                @merge="mergePatients">
                <template slot="data" slot-scope="props">
                    <td class="no-ellipsis">
                        <div class="has-icon">
                            <span class="flex-expand">
                                {{ props.item.full_name }}
                            </span>
                            <svg-icon 
                                name="info-alt" 
                                class="icon-tiny icon-grey"
                                @click.stop="goCabinet(props.item)" />
                        </div>
                    </td>
                    <td>{{ props.item.card_number }}</td>
                    <td class="no-ellipsis">{{ $formatter.listFormat(props.item.getArchivedCards(), 'number') }}</td>
                    <td>{{ props.item.location }}</td>
                    <td>{{ $formatter.phoneNumberFormat(props.item.primary_phone_number) }}</td>
                    <td>{{ $formatter.phoneNumberFormat(props.item.secondary_phone_number) }}</td>
                    <td class="no-ellipsis">{{ $formatter.listFormat(props.item.clinic_names) }}</td>
                    <td>{{ $formatter.dateFormat(props.item.last_visit_date) }}</td>
                    <td>{{ props.item.appointments_count }}</td>
                    <td>{{ props.item.calls_count }}</td>
                    <td>{{ props.item.payments_count }}</td>
                    <td class="no-dash" v-html="getHasRels(props.item)"></td>
                    <td class="no-dash">
                        <svg-icon
                            v-if="props.item.black_mark"
                            name="warning"
                            :title="__('Черная метка: {mark}', {mark: getBlackMarkInfo(props.item)})"
                            class="icon-tiny" />
                        <svg-icon
                            v-if="props.item.is_skk"
                            name="info-alt"
                            :title="__('СКК: {mark}', {mark: getSkkInfo(props.item)})"
                            class="icon-tiny icon-blue" />
                        <svg-icon
                            v-if="props.item.is_attention"
                            name="warning"
                            :title="__('Внимание: {mark}', {mark: props.item.attention_comment})"
                            class="icon-tiny icon-red" />
                    </td>
                    <td>{{ props.item.source_name }}</td>
                    <td class="no-dash" v-html="getHasDocuments(props.item)"></td>
                </template>
            </groups-table>
        </section>
        <section 
            v-else
            class="grey-cap shrinkable" style="padding-top: 80px">
            <wait-search-placeholder 
                :message="__('Выберите критерии соответствия и нажмите «Поиск»')" />
        </section>
    </page>
</template>


<script>
import PatientRepository from '@/repositories/patient';
import CONSTANT from '@/constants';
import DuplicatesMixin from './mixins/duplicates';
import PatientFilter from './patients/Filter.vue';

const ARCHIVE_CARD_SCORE = 3;
const CARD_SCORE = 3;
const CLINIC_SCORE = 5;
const VISIT_SCORE = 5;
const RELATIVES_SCORE = 5;

export default {
    mixins: [
        DuplicatesMixin,
    ],
    components: {
        PatientFilter
    },
    data() {
        return {
            repository: new PatientRepository(),
            columns: [
                {title: __('ФИО')},
                {title: __('№ карты'), width: '5%'},
                {title: __('№ карты (архив)'), width: '5%'},
                {title: __('Город'), width: '7%'},
                {title: __('Телефон'), width: '7%'},
                {title: __('Доп. телефон'), width: '7%'},
                {title: __('Клиника'), width: '8%'},
                {title: __('Последний визит'), width: '6%'},
                {title: __('Записи'), width: '4%'},
                {title: __('Звонки'), width: '4%'},
                {title: __('Платежи'), width: '4%'},
                {title: __('Родств.'), width: '5%'},
                {title: __('Метки'), width: '5%'},
                {title: __('Источник'), width: '8%'},
                {title: __('Документы'), width: '5%'},
            ],
            loading: false,
            displayFilter: true,
            displayTable: false,
            filters: {},
            scores: {},
        };
    },
    computed: {
        totalText() {
            return __('Найдено {count} групп схожих пациентов', {count: this.groups.length});
        },
    },
    methods: {
        mergePatients() {
            this.merge().then((ok) => {
                if (ok) {
                    this.$info(__('Пациенты успешно объединены'));
                }
            });
        },
        startSearch(filters) {
            this.filters = filters;
            this.page = 1;
            this.displayTable = true;
            this.loadDuplicates();
        },
        loadDuplicates() {
            this.loading = true;
            let filters = _.pickBy(this.filters, (v, k) => k !== 'compare_criterias');
            let compareCriterias = this.filters.compare_criterias;
            this.repository.fetchDuplicated(compareCriterias, filters, [
                'contact_details', 
                'clinics', 
                'source', 
                'count_appointments',
                'last_visit_date',
                'count_calls',
                'count_documents',
                'count_payments',
                'archive_card_numbers',
                'relatives',
            ], this.page, this.chunkSize).then((result) => {
                this.groups = this.createGroups(result);
                this.loading = false;
            });
        },
        pickMainItem(group) {
            return _.maxBy(group, i => this.getItemScore(i)).id;
        },
        getHasRels(item) {
            return this.$formatter.boolToString(item.relatives.length !== 0, '<span class="check-yes" />');
        },
        getHasDocuments(item) {
            return this.$formatter.boolToString(item.uploaded_documents_count > 0, '<span class="check-yes" />');
        },
        getBlackMarkInfo(item) {
            return this.$formatter.listFormat([
                this.$handbook.getOption('black_mark_reason', item.black_mark_reason),
                item.black_mark_comment,
            ]);
        },
        getSkkInfo(item) {
            return this.$formatter.listFormat([
                this.$handbook.getOption('skk_reason', item.skk_reason),
                item.skk_comment,
            ]);
        },
        goCabinet(item) {
            let routeData = this.$router.resolve({name: 'patient-cabinet', params: {patientId: item.id}});
            window.open(routeData.href, '_blank');
        },
        getItemScore(item) {
            if (this.scores[item.id] !== undefined) {
                return this.scores[item.id];
            }
            
            let score = 0;
            
            score += CLINIC_SCORE * item.clinics.length;
            score += item.appointments_count;
            score += item.calls_count;
            score += item.last_visit_date ? VISIT_SCORE : 0;
            score += CARD_SCORE * item.cardsSpecializations.length;
            score += ARCHIVE_CARD_SCORE * item.getArchivedCards().length;
            score += item.relatives.length !== 0 ? RELATIVES_SCORE : 0;
            score += item.uploaded_documents_count;
            score += item.payments_count;
            
            return this.scores[item.id] = score;
        },
    },
};
</script>