<template>
    <section class="grey pb-0">
        <el-row :gutter="20">
            <el-col :span="4">
                <form-select
                    :entity="input"
                    :options="clinics"
                    :disabled="disabled"
                    property="clinic"
                    :filterable="true"
                    :placeholder="__('Выберите клинику')"
                />
            </el-col>
            <el-col :span="4">
                <form-select
                    :entity="input"
                    :disabled="disabled"
                    :options="price_sets"
                    property="set_id"
                    :placeholder="__('Выберите набор цен')"
                />
            </el-col>
            <el-col :span="8">
                <form-row
                    name="file"
                    label="">
                    <div class="el-upload el-upload--text">
                        <button
                            type="button"
                            :disabled="!canPickFile || disabled"
                            class="el-button el-button--primary"
                            @click="selectFile">
                            <span>{{ __('Выберите файл') }}</span>
                        </button>
                        <input
                            ref="file"
                            type="file"
                            name="file"
                            class="el-upload__input"
                            @change="pickFile" />
                    </div>
                    <el-button
                        class="ml-20"
                        @click="$emit('reset')">
                        {{ __('Сброс') }}
                    </el-button>
                </form-row>
            </el-col>
        </el-row>
    </section>
</template>

<script>
import PriceSetRepository from '@/repositories/price/set';
import CONSTANTS from '@/constants';

export default {
    props: {
        input: Object,
        clinics: Object,
        disabled: Boolean,
    },
    data() {
        return {
            price_sets: [],
        }
    },
    computed: {
        canPickFile() {
            return this.input.clinic !== null
                && this.input.set_id !== null;
        }
    },
    mounted() {
        this.getPriceSets();
    },
    methods: {
        getPriceSets() {
            let set = new PriceSetRepository();
            let filters = {
                type: [CONSTANTS.PRICE.SET_TYPE.BASE, CONSTANTS.PRICE.SET_TYPE.CERTIFICATE],
            };

            set.fetchList(filters).then(response => {
                this.price_sets = response.map(s => {
                    return {
                        id: s.id,
                        value: this.$handbook.getOption('price_set', s.type),
                    };
                });
                if (this.price_sets[0]) {
                    this.$emit('sets-loaded', this.price_sets[0].id);
                }
            });
        },
        selectFile() {
            if (this.canPickFile) {
                this.$refs.file.value = null;
                this.$refs.file.click();
            }
        },
        pickFile(e) {
            let files = e.target.files;
            if (files.length !== 0) {
                this.$emit('filepick', files[0]);
            }
        }
    },
};
</script>
