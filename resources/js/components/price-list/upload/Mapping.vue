<template>
    <div>
        <div class="sections-wrapper">
            <section class="grey">
                <el-row>
                    <el-col :span="8">
                        <form-select
                            control-size="mini"
                            :entity="input"
                            :options="sheets"
                            property="sheetId"
                            :placeholder="__('Выберите лист')"
                            css-class="form-input mb-0"
                        />
                    </el-col>
                </el-row>
            </section>
            <section class="price-grid">
                <manage-table 
                    v-if="options.length !== 0"
                    :fields="fields"
                    :repository="repository"
                    :enable-loader="false">
                    <template 
                        slot="index" 
                        slot-scope="props">
                        <form-select
                            control-size="mini"
                            :entity="props.rowData"
                            :options="options"
                            property="index"
                            :placeholder="__('Выберите столбец')"
                        />
                    </template>
                </manage-table>
            </section>
        </div>
        <div 
            class="form-footer text-right">
            <el-button 
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="confirm">
                {{ __('Готово') }}
            </el-button>
        </div>
    </div>
</template>

<script>
import ProxyRepository from '@/repositories/proxy-repository';

export default {
    props: {
        workbook: Object,
        mapping: Array,
    },
    data() {
        return {
            sheets: [],
            input: {
                sheetId: null,
            },
            options: [],
            fields: [
                {
                    name: 'title',
                    title: __('Название поля'),
                    width: '50%',
                },
                {
                    name: 'index',
                    title: __('Столбец'),
                    width: '50%',
                },
            ],
            repository: new ProxyRepository(() => Promise.resolve({
                rows: this.mapping,
            })),
        };
    },
    mounted() {
        this.workbook.eachSheet((worksheet, sheetId) => {
            this.sheets.push({
                id: sheetId,
                value: worksheet.name,
            });
            if (this.input.sheetId === null) {
                this.input.sheetId = sheetId;
            }
        });
        this.collectColumns();
    },
    methods: {
        confirm() {
            this.$emit('confirm', {data: this.collectData(), mapping: this.mapping});
        },
        cancel() {
            this.$emit('cancel');
        },
        collectColumns() {
            this.options = [];
            let worksheet = this.workbook.getWorksheet(this.input.sheetId);
            let columns = worksheet.getRow(1).values;
            this.options.push({
                id: -1,
                value: __('Отсутствует'),
            });
            for (let i = 0; i < columns.length; i++) {
                if (columns[i]) {
                    this.options.push({
                        id: i,
                        value: columns[i],
                    });
                }
            }
            this.mapping.forEach((map) => {
                if (map.index >= columns.length) {
                    map.index = -1;
                }
            });
        },
        collectData() {
            let sheetData = [];
            let worksheet = this.workbook.getWorksheet(this.input.sheetId);
            let isHeader = true;
            worksheet.eachRow(function(row, rowNumber) {
                if (isHeader) {
                    isHeader = false;
                } else {
                    sheetData.push(row.values);
                }
            });
            return sheetData;
        },
    },
    watch: {
        ['input.sheetId']() {
            this.$nextTick(() => {
                this.collectColumns();
            });
        },
    },
};
</script>