<template>
    <div
        v-if="hasDiagnostics"
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
                <span
                    v-if="props.rowData.appointment_service_count == 0"
                    @click="remove(props.rowData)">
                    <svg-icon name="delete" class="icon-small icon-blue" />
                </span>
            </template>
        </manage-table>
        <div class="block-total">{{ __('На сумму:') }} <span>{{ $formatter.numberFormat(total) }}</span></div>
    </div>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';
import AssignedService from '@/models/patient/assigned-service';
import OutclinicService from '@/models/patient/outclinic-service';

export default {
    props: {
        diagnostics: {
            type: Array,
            default: () => [],
        },
        outclinicDiagnostics: {
            type: Array,
            default: () => [],
        },
        title: {
            type: [Boolean, String],
            default: __('Назначена аппаратная диагностика'),
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
                    rows: [...this.diagnostics, ...this.outclinicDiagnostics]
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Диагностика'),
                },
                {
                    name: 'comment',
                    title: __('Комментарий'),
                    width: "290px",
                    dataClass: 'no-dash',
                },
                ...(this.readonly ? [] : [{
                        name: 'remove',
                        title: '',
                        width: "30px",
                        dataClass: 'no-ellipsis',
                }]),
            ],
        }
    },
    computed: {
        hasDiagnostics() {
            return this.diagnostics.length !== 0 || this.outclinicDiagnostics.length !== 0;
        },
        total(){
            return this.diagnostics.reduce((total, row) => {
                return total + (Number(row.cost));
            }, 0);
        }
    },
    watch: {
        ['diagnostics']() {
            if (this.$refs.table) {
                this.$refs.table.refresh();
            }
        },
    },
    methods: {
        remove(row) {
            if (row instanceof AssignedService) {
                let index = this.diagnostics.findIndex(item => item.service_id == row.service_id);
                if (index != -1) {
                    return this.$emit('remove-diagnostic', index);
                }
            }

            if (row instanceof OutclinicService) {
                let index = this.outclinicDiagnostics.findIndex(item => item.id == row.id);
                if (index != -1) {
                    return this.$emit('remove-outclinic-diagnostic', index);
                }
            }
        },
    }
}
</script>
