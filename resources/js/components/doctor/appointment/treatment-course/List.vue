<template>
    <manage-table
        ref="table"
        :fields="fields"
        :repository="repository">
        <template 
            slot-scope="props" 
            slot="name">
            <a 
                href="#"
                @click.prevent="showDetails">
                {{ props.rowData.name }}
            </a>
        </template>
        <template 
            slot-scope="props" 
            slot="courseClose">
            <el-tooltip
                v-if="notCompleted(props.rowData)"
                placement="bottom"
                effect="light"
                popper-class="light-popover-content"
                :content="__('Закрыть текущий курс лечения')">
                <a 
                    href="#"
                    @click.prevent="courseClose">
                    {{ __('Закрыть курс') }}
                </a>
            </el-tooltip>
            <el-tooltip
                v-else
                placement="bottom"
                effect="light"
                popper-class="light-popover-content"
                :content="__('Продлить курс лечения')">
                <a 
                    href="#"
                    @click.prevent="courseContinue">
                    {{ __('Продлить курс') }}
                </a>
            </el-tooltip>
        </template>
    </manage-table>
</template>
<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        course: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return  Promise.resolve({
                            rows: [this.course],
                        });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название курса лечения'),
                    width: '55%',
                },
                {
                    name: 'start',
                    title: __('Дата начала'),
                    width: '15%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                {
                    name: 'end',
                    title: __('Дата окончания'),
                    width: '15%',
                    formatter: (val) => {
                        return this.$formatter.dateFormat(val);
                    }
                },
                {
                    name: 'courseClose',
                    title: '',
                    width: '15%',
                    dataClass: 'no-dash',
                }
            ],
        }
    },
    methods: {
        showDetails() {
            this.$emit('show-details');
        },
        courseClose() {
            this.$emit('course-close');
        },
        courseContinue() {
            this.$emit('course-continue');
        },
        refresh() {
            this.$refs.table.refresh();
        },
        notCompleted(course) {
            return _.isVoid(course.end);
        },
    }
}
</script>