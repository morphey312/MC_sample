<template>
    <div class="table-settings">
        <div class="table-settings-wrapper">
            <el-popover
                ref="popover"
                min-width="200px"
                placement="top-end"
                popper-class="anchored-popover"
                :append-to-body="false"
                trigger="click" >
                <svg-icon slot="reference" name="cog-alt" class="icon-small icon-blue" />
                <el-collapse 
                    v-model="activeSetting" 
                    accordion
                    class="table-settings-select">
                    <el-collapse-item 
                        :title="__('Настройки таблицы')" 
                        :name="1">
                            <el-checkbox-group 
                                ref="tableSettings"
                                v-model="selectedFields">
                                <div
                                    v-for="(field, index) in fieldList"
                                    :key="index" >
                                    <el-checkbox
                                        :label="field.name"
                                        :disabled="field.disabled" >
                                        {{ field.title }}
                                    </el-checkbox>
                                </div>
                            </el-checkbox-group>
                            <div class="text-center mb-10">
                                <el-button 
                                    size="mini"
                                    @click="fieldsChanged">
                                    {{ __('Применить') }}
                                </el-button>
                            </div>
                    </el-collapse-item>
                    <el-collapse-item 
                        v-if="showPageSizeSelector"
                        :title="__('Настройки пагинации')" 
                        :name="2">
                        <div
                            v-for="item in pageSizeOptions"
                            :key="item"
                            :class="{'page-size-selected': (pageSize == item)}">
                            <a href="#" @click.prevent="pageSizeChanged(item)">
                                {{ __('Показывать {rowCount} строк', {rowCount: item}) }}
                            </a>
                        </div>
                    </el-collapse-item>
                </el-collapse>
            </el-popover>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        visibleFields: {
            type: Array,
        },
        fields: {
            type: Array,
        },
        pageSizeOptions: {
            type: Array,
            default: () => [
                25,
                50,
                75,
                100,
            ],
        },
        pageSize: {
            type: [Number, String],
            default: 0,
        },
        showPageSizeSelector: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            activeSetting: null,
            selectedFields: this.getSelectedFields(),
            wrapperId: null,
        }
    },
    beforeMount() {
        this.wrapperId = parseInt(Math.random() * 10000);
        this.fieldList = this.getFieldList();
        this.verifySeletedFields();
    },
    watch: {
        selectedFields() {
            this.verifySeletedFields();
        }
    },
    methods: {
        pageSizeChanged(val) {
            this.$emit('pageSizeChanged', val);
            this.$refs.popover.doClose();
        },
        getFieldList() {
            return this.fields.filter(field => _.isFilled(field.title) && field.configurable !== false);
        },
        verifySeletedFields() {
            if(this.selectedFields.length === 1 || this.fieldList.length === 1) {
                this.setDisabledForSingleField();
            } else {
                this.fieldList.map(field => delete field.disabled);
            }
        },
        setDisabledForSingleField() {
            let option = this.fieldList.find(field => field.name === this.selectedFields[0]);
            if (option) {
                option.disabled = true;
            }
        },
        getSelectedFields() {
            return this.visibleFields.map(field => field.name);
        },
        showFieldList() {
            this.$nextTick(() => {
                this.$refs.tableSettings.focus();
            });
        },
        fieldsChanged() {
            this.$emit('changed', this.getHiddenFields());
            this.$refs.popover.doClose();
        },
        getHiddenFields() {
            return this.fieldList.filter(field => this.selectedFields.indexOf(field.name) === -1)
                        .map(field => field.name);
        },
    },
}
</script>