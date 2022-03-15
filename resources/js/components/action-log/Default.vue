<template>
    <div :class="sectionClass">
        <manage-table 
            ref="table"
            :fields="fields"
            :filters="filters"
            :repository="repository"
            :flex-height="true"
            :show-table-settings="false"
            @header-filter-updated="syncFilters">
            <template
                slot="changes"
                slot-scope="props" >
                <div v-for="(change, index) in getChanges(props.rowData)"
                    :key="index"
                    class="change-log" >
                    <template v-if="change.old !== null && change.new !== null">
                        <span class="attribute">{{ change.label }}</span> &mdash;
                        {{ __('Изменено: &laquo;') }}<span class="old" v-html="change.old" />{{ __('&raquo; на') }}
                        &laquo;<span class="new" v-html="change.new" />&raquo;
                    </template>
                    <template v-else-if="change.old !== null">
                        <span class="attribute">{{ change.label }}</span> &mdash;
                        {{ __('Удалено: &laquo;') }}<span class="old" v-html="change.old" />&raquo;
                    </template>
                    <template v-else-if="change.new !== null">
                        <span class="attribute">{{ change.label }}</span> &mdash;
                        {{ __('Добавлено: &laquo;') }}<span class="new" v-html="change.new" />&raquo;
                    </template>
                </div>
            </template>
        </manage-table>
    </div>
</template>

<script>
import DateRangeHeaderFilter from '@/components/general/table/DateRangeHeaderFilter.vue';

export default {
    props: {
        sectionClass: {
            type: String,
            default: 'flex-content',
        },
    },
    data() {
        return {
            fields: [
                {
                    name: 'date',
                    filterField: 'date_range',
                    title: __('Дата изменения'),
                    width: '20%',
                    formatter: (value) => {
                        return this.$formatter.datetimeFormat(value);
                    },
                    filter: DateRangeHeaderFilter,
                },
                {
                    name: 'user_name',
                    title: __('Кто внес изменения'),
                    width: '20%',
                    filter: true,
                },
                {
                    name: 'changes',
                    title: __('Что изменилось'),
                    dataClass: 'no-ellipsis',
                    width: '60%',
                },
            ],
            filters: {
            },
        };
    },
    methods: {
        getChanges(row) {
            let changes = [];
            let keys = _.uniq(_.concat(
                Object.keys(row.new), 
                Object.keys(row.old)
            )).sort();
            keys.forEach((key) => {
                if (_.isArray(row.old[key]) && _.isArray(row.new[key])) {
                    let added = _.difference(row.new[key], row.old[key]);
                    let removed = _.difference(row.old[key], row.new[key]);
                    if (added.length !== 0) {
                        changes.push({
                            label: this.fieldLabel(key),
                            old: null,
                            new: this.formatFieldValue(key, added),
                        });
                    }
                    if (removed.length !== 0) {
                        changes.push({
                            label: this.fieldLabel(key),
                            old: this.formatFieldValue(key, removed),
                            new: null,
                        });
                    }
                } else {
                    changes.push({
                        label: this.fieldLabel(key),
                        old: this.formatFieldValue(key, row.old[key]),
                        new: this.formatFieldValue(key, row.new[key]),
                    });
                }
            });
            return changes;
        },
        fieldLabel(name) {
            if (typeof this.attributes[name] === 'string') {
                return this.attributes[name];
            }
            if (typeof this.attributes[name] === 'object') {
                return this.attributes[name].label;
            }
            return name;
        },
        formatFieldValue(name, value) {
            if (value === undefined) {
                return null;
            }
            if (typeof this.attributes[name] === 'object' && this.attributes[name].formatter !== undefined) {
                value = this.attributes[name].formatter(value);
            }
            return _.isVoid(value) ? null : value;
        },
        syncFilters(updates) {
            this.filters = _.onlyFilled({...this.filters, ...updates});
        },
    },
}
</script>