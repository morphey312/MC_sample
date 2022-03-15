<template>
    <div
        v-if="nextVisit"
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
            <template slot="delete" slot-scope="props">
                <a @click.prevent="remove">
                    <svg-icon name="delete" class="icon-small icon-blue" />
                </a>
            </template>
        </manage-table>
    </div>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        nextVisit: Object,
        title: {
            type: [Boolean, String],
            default: __('Дата следующего визита'),
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
                    rows: this.nextVisit === null ? [] : [{
                        title: __('Рекомендована дата следующего визита'),
                        date: this.nextVisit.next_visit_date,
                    }],
                });
            }),
            fields: [
                {
                    name: 'title',
                    title: __('Рекомендация'),
                },
                {
                    name: 'date',
                    title: __('Дата'),
                    width: '100px',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                ...(this.readonly ? [] : [{
                    name: 'delete',
                    title: '',
                    dataClass: 'no-ellipsis',
                    width: '30px',
                }]),
            ],
        }
    },
    methods: {
        remove() {
            this.nextVisit.delete().then(() => {
                this.$info(__('Запись о следующем визите удалена успешно !'));
                this.$emit('next-visit-removed');
            }).catch(() => {
                this.$error(__('Не удалось удалить запись о следующем визите'));
            });
        },
    },
    watch: {
        ['nextVisit.next_visit_date']() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
}
</script>
