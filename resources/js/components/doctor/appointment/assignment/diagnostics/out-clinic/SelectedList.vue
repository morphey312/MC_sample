<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filter"
        :repository="repository"
        table-height="auto" >
        <template
            slot="comment"
            slot-scope="props">
            <el-input
                type="textarea"
                autosize
                :rows="1"
                class="table-textarea"
                :placeholder="__('Добавить комментарий')"
                v-model="props.rowData.comment"/>
        </template>
        <template
            slot="remove"
            slot-scope="props" >
            <span class="" @click="toggleSelection(props.rowIndex)">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import TableFilter from '@/mixins/appointment/analysis/static-table-filter';

export default {
    mixins: [
        TableFilter
    ],
    props: {
        rows: {
            type: Array,
            default: () => []
        },
        readonly: Boolean,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.rows
                })
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Диагностика вне клиники'),
                },
                {
                    name: 'comment',
                    title: __('Дополнительные примечания'),
                    width: "360px",
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
    watch: {
        rows() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
    methods: {
        toggleSelection(index) {
            this.$emit('selection-changed', index);
        },
    }
}
</script>
