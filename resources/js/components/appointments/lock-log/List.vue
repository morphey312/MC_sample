<template>
    <manage-table
        ref="table"
        :fields="fields"
        :filters="filters"
        :repository="repository"
        :scopes="scopes"
        :initial-sort-order="initialSortOrder"
        :selectable-rows="true"
        @selection-changed="selectionChanged"
        :flex-height="true"
        @loaded="loaded">
        <template
            slot="id"
            slot-scope="props">
            <div class="has-icon">

                <template >
                    <svg-icon
                        name="info-alt"
                        class="icon-tiny icon-grey"
                         />
                </template>
            </div>
        </template>
         <template
            slot="updated_at"
            slot-scope="props">
            <span>
                {{ (props.rowData.status == 1 ? "-" : $formatter.dateFormat(props.rowData.updated_at)) }}
            </span>
        </template>
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
import LockLogRepository from '@/repositories/locklog';

export default {
    props: {
        filters: Object,
    },

    data() {
        return {
            select: null,
            repository: new LockLogRepository(),
            fields: [

                {
                    name: 'clinic_name',
                    title: __('Клиника'),
                    width: '100px',

                },
                {
                    name: 'status',
                    title: __('Статус блокировки'),
                    width: '100px',
                    formatter: (value) => {
                        return (value == 1) ? __('активна') : __('снята');
                    },

                },
                {
                    name: 'daysheet.date',
                    title: __('Дата блока'),
                    width: '100px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },

                },

                {
                    name: 'start',
                    title: __('Начало'),
                    width: '100px',

                },
                {
                    name: 'end',
                    title: __('Конец'),
                    width: '100px',

                },

                {
                    name: 'doctor_full_name',
                    title: __('Врач'),
                    width: '150px',
                },
                {
                    name: 'reason',
                    title: __('Причина блокировки'),
                    width: '100px',

                },

                {
                    name: 'comment',
                    title: __('Комментарий'),
                    width: '100px',

                },

                {
                    name: 'employee_name',
                    title: __('Оператор'),
                    width: '150px',

                },

                {
                    name: 'created_at',
                    title: __('Дата создания'),
                    width: '100px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },

                },
                {
                    name: 'employee_off_name',
                    title: __('Оператор снявший блок'),
                    width: '150px',

                },

                {
                    name: 'updated_at',
                    title: __('Дата снятия'),
                    width: '100px',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    },

                },
                {
                    name: 'reason_off',
                    title: __('Причина разблокировки'),
                    width: '100px',

                },

                {
                    name: 'comment_off',
                    title: __('Комментарий снятия блокировки'),
                    width: '100px',

                },









            ],
            initialSortOrder: [
                {field: 'created', direction: 'asc'},
            ],
             scopes: [
                 "doctor",
                 "employee",
                 "employee_off",
                 "reason",
                 "reason_off",
                 "clinic"
            ],
        }
    },
    methods: {
        loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            if(selection && selection.length) {
                this.select = selection[0];
            }
            else {
                this.select = null;
            }
            this.$emit("selection", this.select)

        }
    },
}
</script>
