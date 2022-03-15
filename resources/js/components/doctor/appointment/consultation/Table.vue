<template>
    <div 
        v-if="consultations.length !== 0"
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
    </div>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        consultations: {
            type: Array,
            default: () => [],
        },
        title: {
            type: [Boolean, String],
            default: __('Назначена консультация врача'),
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
                    rows: this.consultations,
                });
            }),
            fields: [
                {
                    name: 'specialization_name',
                    title: __('Выбранные специализации'),
                },
                {
                    name: 'comment',
                    title: __('Дополнительные примечания'),
                    width: "300px",
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
        consultations() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
    methods: {
        remove(index) {
            this.$emit('remove-selection', index);
        },
    }
}
</script>