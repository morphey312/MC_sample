<template>
    <div class="sections-wrapper">
        <section class="grey">
            <el-row :gutter="20">
                <el-col :span="12">
                    <el-input 
                        :placeholder="__('Введите ЕГРПОУ')" 
                        v-model="edrpou" />
                </el-col>
                <el-col :span="12">
                    <el-button
                        type="primary"
                        :disabled="edrpou.length !== 8 && edrpou.length !== 10"
                        @click="search">
                        {{ __('Поиск') }}
                    </el-button>
                </el-col>
            </el-row>
        </section>
        <section>
            <manage-table
                v-if="searched"
                ref="table"
                :fields="fields"
                :repository="repository"
                :selectable-rows="true"
                :enable-pagination="false"
                @selection-changed="setActiveItem">
            </manage-table>
            <wait-search-placeholder 
                v-else
                :can-create="false" />
        </section>
        <div class="dialog-footer text-right">
            <el-button
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                :disabled="activeItem === null"
                @click="next">
                {{ __('Далее') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';
import ehealth from '@/services/ehealth';

export default {
    data() {
        return {
            repository: new ProxyRepository(() => {
                return ehealth.getLegalEntities({edrpou: this.edrpou}).then((list) => {
                    return {
                        rows: list,
                    }
                });
            }),
            fields: [
                {
                    name: 'name',
                    title: __('Название'),
                },
                {
                    name: 'type',
                    width: '40%',
                    title: __('Тип'),
                    formatter: (value) => {
                        return this.$handbook.getOption('msp_type', value);
                    },
                },
            ],
            activeItem: null,
            edrpou: '',
            searched: false,
        };
    },
    methods: {
        search() {
            if (this.searched) {
                this.$refs.table.refresh();
            } else {
                this.searched = true;
            }
        },
        cancel() {
            this.$emit('cancel');
        },
        next() {
            this.$emit('next', this.activeItem);
        },
        setActiveItem(selection) {
            this.activeItem = selection.length !== 0 ? selection[0] : null;
        }
    }
}
</script>