<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :enable-pagination="false"
        table-height="auto" >
        <template
            slot="comment"
            slot-scope="props">
            <el-input
                type="textarea"
                autosize
                :rows="1"
                class="table-textarea"
                :placeholder="__('Добавить дополнительное описание')"
                v-model="props.rowData.comment"/>
        </template>
        <template
            slot="remove"
            slot-scope="props">
            <span class="" @click="toggleSelection(props.rowIndex)">
                <svg-icon name="delete" class="icon-small icon-blue" />
            </span>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        readonly: Boolean,
        rows: {
            type: Array,
            default: () => []
        },
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
                    name: 'specialization_name',
                    title: __('Выбранные специализации'),
                    dataClass: 'text-left',
                },
                {
                    name: 'comment',
                    title: __('Дополнительные примечания'),
                    width: "450px",
                    dataClass: 'text-center',
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