<template>
    <section v-loading="loading">
        <el-row :gutter="20" class="mt-10">
            <el-col :span="24">
                <div class="table-list">
                    <div class="table-list__top d-flex">
                         <div class="table-list__top-title d-flex column1">{{ __('Код анализа') }}</div>
                         <div class="table-list__top-title d-flex column8">{{ __('Лаборатория') }}</div>
                         <div class="table-list__top-title d-flex column2">{{ __('Название анализа') }}</div>
                         <div class="table-list__top-title d-flex column3">{{ __('Тип контейнера') }}</div>
                         <div class="table-list__top-title d-flex column4">{{ __('Биоматериал') }}</div>
                         <div class="table-list__top-title d-flex column5">{{ __('Емкость') }}</div>
                         <div class="table-list__top-title d-flex column6">{{ __('Присвоить номер анализа') }}</div>
                    </div>
                    <div v-for="(patient,k) in items" class="table-list__content d-flex flex-wrap" :key="k">
                        <div><b>{{ patient[0].patient_name }}</b></div>
                        <div class="table-list__item d-flex flex-wrap mb-10"
                            v-for="(analysis, index) in patient" :key="index">
                            <template v-if="analysis.containers.length > 0">
                                <div v-for="(container, key) in analysis.containers" :key="key"
                                    class="accordion-content d-flex flex-wrap transition">
                                    <div class="accordion-content__row d-flex flex-wrap"
                                        style="width:100%"
                                        @click="selectionChanged(container)"
                                        :class="{ active: (currentContainer && (currentContainer.result_id === container.result_id) && (currentContainer.random === container.random)) }">
                                        <div class="accordion-content__column column1 d-flex flex-wrap">{{ analysis.code }}</div>
                                        <div class="accordion-content__column column8 d-flex flex-wrap">{{ listFormatter(analysis.laboratory_names) }}</div>
                                        <div class="accordion-content__column column2 d-flex flex-wrap">
                                            <span>{{ analysis.name }}</span>
                                            <div v-if="analysis.payed === 0" class="has-icon" >
                                                <el-popover
                                                    placement="bottom"
                                                    min-width="40px"
                                                    trigger="click">
                                                    <p><b class="color-danger">{{ __('Анализ не оплачен') }}</b></p>
                                                    <template slot="reference">
                                                        <svg-icon name="info-alt" class="icon-tiny icon-red" />
                                                    </template>
                                                </el-popover>
                                            </div>
                                        </div>
                                        <div class="accordion-content__column column3 d-flex flex-wrap">{{ container.name }}</div>
                                        <div class="accordion-content__column column4 d-flex flex-wrap">{{ container.biomaterial.name }}</div>
                                        <div class="accordion-content__column column5 d-flex flex-wrap">{{ container.value}}</div>
                                        <div class="accordion-content__column column6 d-flex flex-wrap">
                                            <form-input
                                                :entity="container"
                                                :disabled="true"
                                                :ref="`single-${key}-container-${index}-${container.container_id}`"
                                                property="barcode"/>
                                            <svg-icon name="dismiss-alt" style="position:absolute; right:125px" class="icon-small icon-red"  @click="clearBarcode()"
                                                v-if="(currentContainer  && container.random === currentContainer.random)"/>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </el-col>
        </el-row>
        <hr>
        <el-row :gutter="20" class="mt-10">
            <div>{{ __('Итого в заказе (типы емкостей), шт:') }}</div>
            <el-carousel class="products-list col-12"
                :loop="true"
                type="card"
                :autoplay="false"
                arrow="never"
                trigger="click"
                ref="carousel"
                height="180px">
                    <el-carousel-item
                        :name="`container_${index}`"
                        class="products-list__item d-flex" v-for="(item,index) in containers" :key="index">
                            <div class="products-list__item-inner d-flex flex-wrap"
                                v-bind:class="{active : (currentContainer && index == currentContainer.container_id)}">
                                <div class="products-list__item-image d-flex" v-html="item[0].image_data"/>
                                <div class="products-list__item-info">
                                    <span class="products-list__item-count"> {{item.length}} {{ __('шт.') }}</span>
                                    <span class="products-list__item-title">{{ item[0].name }}</span>
                                </div>
                            </div>
                    </el-carousel-item>
            </el-carousel>
        </el-row>
            <div class="form-footer text-right">
                <el-button
                    @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    @click="register">
                    {{ __('Зарегистрировать и сформировать заказ') }}
                </el-button>
            </div>
    </section>
</template>

<script>
import LaboratoryClientRepository from '@/repositories/analysis/laboratory/client';
import AnalysisItem from '@/models/analysis/laboratory/order/item';
import BatchRequest from '@/services/batch-request';
import ContainerRepository from "@/repositories/analysis/laboratory/order/item/container";

export default {
    props: {
        laboratoryAnalyses: {
            type: Array,
            default: () => [],
        },
    },
    beforeDestroy() {
        document.removeEventListener('keyup', this.onKeydown, false);
    },
    mounted() {
        document.addEventListener('keyup', this.onKeydown);
        this.getAnalyses();
    },
    data() {
        return {
            containers: [],
            currentContainer: null,
            items: [],
            barcode: '',
            loading: true,
            batchRequest: new BatchRequest('/api/v1/analysis/laboratories/orders/items/batch'),
            batchRequestPostponed: new BatchRequest('/api/v1/analysis/laboratories/orders/items/batch/postponed'),
        }
    },
    computed: {
        actualBarcodes() {
            let barcodes = []
            this.items.forEach(patient => {
                patient.forEach(analysis => {
                    analysis.containers.forEach(container => {
                        if (container.barcode && container.barcode.length > 0) {
                            barcodes.push(container.barcode)
                        }
                    })
                })
            })

            return barcodes
        }
    },
    methods: {
        listFormatter(value) {
            return this.$formatter.listFormat(value)
        },
        onKeydown(e) {
            if (this.isNumberKey(e) && e.keyCode != 13) {
                this.barcode += e.key;
            } else if(e.keyCode === 13) {
                this.checkContainerSelected();
            }
        },
        isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode
            if (charCode != 43 && charCode > 31 && (charCode < 48 || charCode > 57))
                return false;
            return true;
        },
        checkContainerSelected() {
            if (this.currentContainer === null) {
                this.$info(__('Пожалуйста выберите контейнер'));
            } else if (this.currentContainer.barcode && this.currentContainer.barcode.length > 0) {
                this.$confirm(__('Внимание, данный контейнер уже отсканирован. Вы действительно хотите просканировать еще раз?'),
                    () => {
                        this.readBarcode();
                    }
                );
            } else {
                this.readBarcode()
            }
        },
        notAvailableBarcodeWarning() {
            this.$info(__('Данный штрихкод уже используется'));
            this.barcode = '';
        },
        clearBarcode() {
            this.currentContainer.barcode = '';
        },
        readBarcode() {
            let repo = new ContainerRepository({filters: {barcode: this.barcode}});
            repo.fetch().then((response) => {
                if (response.rows.length > 0) {
                    this.notAvailableBarcodeWarning();
                } else {
                    if (this.actualBarcodes.includes(this.barcode))
                        return this.notAvailableBarcodeWarning();
                    this.setBarcode();
                }
            });
        },
        setBarcode() {
            this.currentContainer.barcode = this.barcode;
            this.barcode = '';
        },
        cancel() {
            this.$emit('cancel');
        },
        skipping(row, status) {
            row.is_postponed = status;
        },
        findResultAttribute(item, attribute) {
            let result = this.laboratoryAnalyses.find(row => {
                return row.lab_analysis_id === item.lab_analysis_id;
            })

            return (result) ? result[attribute]: null;
        },
        castItems(rows) {
            let items = [];
            this.laboratoryAnalyses.forEach(analysis => {
                let item = {};
                let row = rows.find(row => row.lab_analysis_id === analysis.lab_analysis_id).clone()
                item.code = row.attributes.code;
                item.is_postponed = true;
                item.laboratory_names = row.attributes.laboratory_names;
                item.name = row.attributes.name;
                item.containers = row.attributes.containers;
                item.containers.forEach(container => {
                    container.result_id = analysis.result_id;
                })
                item.patient_name = analysis.patient_name;
                item.result_id = analysis.result_id;
                item.patient_id = analysis.patient_id;
                item.payed = analysis.payed;
                item.lab_analysis_notes = analysis.lab_analysis_notes;

                if (items[analysis.patient_id]) {
                    items[analysis.patient_id].push(item);
                } else {
                    items[analysis.patient_id] = [item];
                }
           });


            return items.filter(function (el) {
                return el != null;
            });
        },
        register() {
            this.loading = true;
            this.batchRequest.reset();
            this.batchRequestPostponed.reset();
            let orderItems = [];
            let skippedItems = [];

            this.items.forEach(patient => {
                patient.forEach(analysis => {
                    if (analysis.is_postponed) {
                        skippedItems.push(analysis)
                    } else {
                        orderItems.push(analysis)
                    }
                });
            });

            console.log(orderItems, skippedItems)

            if (!_.isEmpty(skippedItems)) {
                skippedItems.forEach(item => {
                    this.batchRequestPostponed.create(this.createItem(item));
                })
                console.log(this.batchRequestPostponed);
                this.batchRequestPostponed.submit().then(() => {
                    if (!_.isEmpty(orderItems)) {
                       this.saveOrder(orderItems, skippedItems);
                    } else {
                        this.loading = false;
                        this.$emit('save');
                    }
                });
            } else {
                if (!_.isEmpty(orderItems)) {
                    this.saveOrder(orderItems);
                }
            }
        },
        saveOrder(items, skippedItems = []) {
            this.batchRequest.reset();
            items.forEach(item => {
                this.batchRequest.create(this.createItem(item));
            })
            this.batchRequest.submit().then(() => {
                this.$info(__('Биоматериал успешно зарегистрирован'));
                this.$emit('save', );
            }).catch((e) => {
                this.$warning(__('Что-то пошло не так'));
            });
            this.loading = false;
        },
        createItem(item) {
            return new AnalysisItem(item);
        },
        addAppointmentAnalyses() {
             this.$emit('addAnalyses');
        },
        getAnalyses() {
            let client = new LaboratoryClientRepository();
            client.fetch(this.getAnalysesFilter(), null, ['containers'], 1, 50,'analysis').then(response => {
                this.items = this.castItems(response.rows);
                this.groupContainers();
                this.loading = false;
            });
        },
        getAnalysesFilter() {
            let ids = this.laboratoryAnalyses.map(item => {
                return item.lab_analysis_id;
            });
            return {id: _.uniq(ids)}
        },
        selectionChanged(container) {
            this.currentContainer = container;
        },
        getImage() {
            fileLoader.get(this.attachment.url).then((blobUrl) => {
                this.src = blobUrl;
            });
        },
        groupContainers() {
            let containers = [];
            this.items.forEach(patient => {
                patient.forEach(analysis => {
                    analysis.containers.forEach(container => {
                        containers.push(container);
                    });
                });
            })
            if(containers) {
                this.containers = _.groupBy(containers, 'container_id');
            }
        },
    }
}
</script>
