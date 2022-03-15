<template>
    <div
        v-loading="loading"
        v-if="!loading">
        <div v-for="(container, index) in containers"
            :key="index"
            class="transfer-details">
            <el-row :gutter="20" class="mt-10">
                <el-col :span="4" style="border: 1px solid #BDBDBD;">
                    <div v-if="images[index]">
                        <div class="image-container text-center" v-html="images[index]"/>
                    </div>
                </el-col>
                <el-col :span="20">
                    <el-table
                        :data="container"
                        style="width: 100%"
                        header-row-class-name="light-grey">
                        <el-table-column
                            :label="__('Анализи')"
                            prop="analysis_names"
                            width="350"
                            class-name="text-left">
                             <template
                                slot-scope="scope">
                                    {{ getNameRows(scope.row.results, 'analysis_names') }}
                            </template>
                        </el-table-column>
                        <el-table-column
                            :label="__('Пациент')"
                            prop="patient.full_name"
                            width="200"
                            class-name="text-left" />
                        <el-table-column
                            :label="__('Штрихкод')"
                            prop="barcode"
                            width="150" />
                    </el-table>
                </el-col>
            </el-row>
        </div>
        <el-row class="dialog-footer text-right">
            <el-button
                class="primary-btn-outline no-margin-t"
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
        </el-row>
    </div>
</template>
<script>
import LaboratoryClientRepository from '@/repositories/analysis/laboratory/client';

export default {
    props: {
        item: {
            type: Object,
            default: () => ({}),
        },
        isTransfer: {
            type: Boolean,
            required: true,
        }
    },
    data() {
        return {
            loading: true,
            containers: {},
            images: {},
            client: new LaboratoryClientRepository(),
        }
    },
    beforeMount() {
        if (this.isTransfer) {
        
            this.item.containers.forEach(container => {
                if (_.isArray(this.containers[container.container_id])) {
                    this.containers[container.container_id].push(container);
                } else {
                    this.containers[container.container_id] = [container];
                }
            });
        } else {
            this.addContainerItems(this.item);
        }
    },
    mounted() {
        this.loadContainerImages();
    },
    methods: {
        loadContainerImages() {
            let containers = Object.keys(this.containers);
            if (containers) {
                this.client.fetch({
                id: containers
                }, null, ['image_data'], 1, 1000,'containers').then(response => {
                    response.rows.forEach(container => {
                        this.images[container.id] = container.image_data
                    })
                    this.loading = false;
            });

            }
        },
        cancel() {
            this.$emit('close');
        },
        getNameRows(row, attribute) {
            let names = row.map(item => item[attribute]);
            return this.$formatter.listFormat(names);
        },
        addContainerItems(order) {
            order.items.forEach(orderItem => {
                orderItem.containers.forEach(itemContainer => {
                    let target = this.containers.find(container => container.id === itemContainer.container_id);
                    let item = {
                        barcode: itemContainer.barcode,
                        analysis_name: orderItem.name,
                        patient_name: orderItem.patient_name,
                    };

                        if (target) {
                        target.order_items.push(item);
                    } else {
                        this.containers.push({
                            id: itemContainer.container_id,
                            image_data: itemContainer.image_data,
                            order_items: [item],
                        });
                    }
                });
            });
        },
    },
}
</script>
