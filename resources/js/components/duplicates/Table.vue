<template>
    <div class="manage-table flex-height">
        <div>
            <div class="vuetable-head-wrapper" style="overflow-x: hidden;">
                <table class="vuetable ui blue selectable unstackable celled table fixed" :style="`width: ${tableWidth}`">
                    <colgroup>
                        <col style="width: 23px;" />
                        <col style="width: 23px;" />
                        <col 
                            v-for="col in columns"
                            :style="`width: ${col.width || 'auto'};`" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th></th>
                            <th>
                                <el-checkbox 
                                    v-model="toggleAll">
                                </el-checkbox>
                            </th>
                            <th
                                v-for="col in columns">
                                {{ col.title }}
                            </th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="vuetable-body-wrapper fixed-header" style="height: 100%">
                <table class="vuetable fixed-header ui blue selectable unstackable celled table fixed" :style="`table-layout: fixed; width: ${tableWidth}`">
                    <colgroup>
                        <col style="width: 23px;" />
                        <col style="width: 23px;" />
                        <col 
                            v-for="col in columns"
                            :style="`width: ${col.width || 'auto'};`" />
                    </colgroup>
                    <tbody v-for="group in groups">
                        <tr v-for="item in group.items">
                            <td>
                                <el-radio 
                                    class="no-label"
                                    v-model="group.main" 
                                    :label="item.id"
                                    @change="updateOthers(group)">
                                </el-radio>
                            </td>
                            <td>
                                <el-checkbox 
                                    :disabled="item.id === group.main"
                                    v-model="group.others[item.id]">
                                </el-checkbox>
                            </td>
                            <slot name="data" :item="item" />
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="table-footer">
            <div class="buttons">
                <el-button
                    :disabled="page === 1"
                    @click="$emit('load-prev')">
                    {{ __('Предыдущие') }} {{ chunkSize }}
                </el-button>
                <el-button
                    :disabled="groups.length < chunkSize"
                    @click="$emit('load-next')">
                    {{ __('Следующие') }} {{ chunkSize }}
                </el-button>
                <div class="float-right">
                    {{ totalText }}
                    <el-button
                        :disabled="groups.length === 0"
                        type="primary"
                        class="ml-10"
                        @click="$emit('merge')">
                        {{ __('Объединить') }}
                    </el-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        groups: Array,
        page: Number,
        chunkSize: Number,
        totalText: String,
        columns: Array,
        tableWidth: {
            type: String,
            default: '100%',
        }
    },
    data() {
        return {
            toggleAll: true,
            lastScrollPosition: 0,
        };
    },
    mounted () {
        if (this.tableWidth !== '100%') {
            let elem = this.$el.getElementsByClassName('vuetable-body-wrapper')[0];
            if (elem != null) {
                elem.addEventListener('scroll', this.handleScroll);
            }
        }
    },
    destroyed () {
        if (this.tableWidth !== '100%') {
            let elem = this.$el.getElementsByClassName('vuetable-body-wrapper')[0];
            if (elem != null) {
                elem.removeEventListener('scroll', this.handleScroll);
            }
        }
    },
    methods: {
        updateOthers(group) {
            group.items.forEach((item) => {
                group.others[item.id] = item.id !== group.main;
            });
        },
        handleScroll (e) {
            let horizontal = e.currentTarget.scrollLeft;
            if (horizontal != this.lastScrollPosition) {
                let header = this.$el.getElementsByClassName('vuetable-head-wrapper')[0]
                if (header != null) {
                    header.scrollLeft = horizontal;
                }
                this.lastScrollPosition = horizontal;
            }
        },
    },
    watch: {
        toggleAll(v) {
            if (v) {
                this.groups.forEach((group) => {
                    this.updateOthers(group);
                });
            } else {
                this.groups.forEach((group) => {
                    group.items.forEach((item) => {
                        group.others[item.id] = false;
                    });
                });
            }
        }
    }
};
</script>