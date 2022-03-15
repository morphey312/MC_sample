<template>
    <div>
        <error-catcher :catch="archivesErrors" />
        <section>
            <manage-table
                ref="table"
                :fields="fields"
                :repository="repository"
                :selectable-rows="true"
                :enable-pagination="false"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
            </manage-table>
            <div class="mt-10">
                <el-button
                    @click="create">
                    {{ __('Добавить') }}
                </el-button>
                <el-button
                    :disabled="activeItem === null"
                    @click="edit">
                    {{ __('Редактировать') }}
                </el-button>
                <el-button
                    :disabled="activeItem === null"
                    @click="remove">
                    {{ __('Удалить') }}
                </el-button>
            </div>
        </section>
    </div>
</template>

<script>
import FormCreate from './archives/FormCreate.vue';
import FormEdit from './archives/FormEdit.vue';
import ProxyRepository from '@/repositories/proxy-repository';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    props: {
        msp: Object,
    },
    data() {
        return {
            repository: new ProxyRepository(() => {
                return Promise.resolve({
                    rows: this.msp.archives,
                });
            }),
            fields: [
                {
                    name: 'date',
                    title: __('Дата'),
                    width: '30%',
                    formatter: (value) => {
                        return this.$formatter.dateFormat(value);
                    },
                },
                {
                    name: 'place',
                    width: '70%',
                    title: __('Местоположение'),
                },
            ],
            archivesErrors: [
                {key: new RegExp('archives[.][0-9]+[.]place'), label: __('Местоположение архива')},
            ],
        };
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить архив'),
                editHeader: __('Изменить архив'),
                width: '300px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить этот архив?'),
                deleted: __('Архив был успешно удален'),
                created: __('Архив был успешно добавлен'),
                updated: __('Архив был успешно обновлен'),
            };
        },
        getManageTable() {
            return this.$refs.table;
        },
        onCreated(model) {
            this.msp.archives.push(model);
        },
        performDelete(model) {
            let index = this.msp.archives.indexOf(model);
            if (index !== -1) {
                this.msp.archives.splice(index, 1);
            }
            return Promise.resolve();
        },
    }
}
</script>