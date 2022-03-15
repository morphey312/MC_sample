<template>
    <section v-loading="loading">
        <el-row :gutter="20" class="mt-10">
            <el-col :span="24">
                <hr>
                <table class="vuetable ui blue unstackable celled fixed text-center table">
                <div>
                    <table class="vuetable bb-0 biomaterial-registration" v-if="showTable">
                        <thead>
                            <th class="sticky-header">
                                {{ __('Код анализа') }}
                            </th>
                            <th class="sticky-header">
                                {{ __('Биоматериал') }}
                            </th>
                            <th class="sticky-header">
                                {{ __('Рекомендованный контейнер') }}
                            </th>
                            <th class="sticky-header">
                                {{ __('Тип контейнера') }}
                            </th>
                            <th class="sticky-header">
                                {{ __('Название анализа') }}
                            </th>
                            <th class="sticky-header">
                                {{ __('Штрих-код') }}
                            </th>
                        </thead>
                        <tbody v-for="(container, index) in containers" 
                            :key="index"
                            v-bind:class="{ active: (currentContainer && (currentContainer === container)) }"
                            @click="selectRow(container)">
                            <tr v-for="(analysis, name) in container.analysis_name" :key="name">
                                <td class="no-ellipsis row-border"
                                    width="120px">
                                        {{ container.analysis_code[name] }}
                                </td>
                                <td class="no-ellipsis row-border"
                                    width="120px">
                                        {{ container.biomaterial_name }}
                                </td>
                                <td class="no-ellipsis"
                                    width="120px"
                                    v-if="name === 0"
                                    :rowspan="container.analysis_name.length"
                                    colspan="1">
                                    {{ container.container_name }}
                                </td>
                                <td class="no-ellipsis"
                                    style="text-align:center;"
                                    v-if="name === 0"
                                    width="190px"
                                    :rowspan="container.analysis_name.length"
                                    colspan="1">
                                    <el-popover
                                        placement="bottom"
                                        min-width="40px"
                                        trigger="click">
                                        <p><b>{{ __('Объем:') }}</b> {{ container.volume }}  {{ container.handbook_measure }}</p>
                                        <template slot="reference">
                                            <span style="margin-bottom: 10px; height: 80px;" v-html="container.container_image"/>
                                        </template>
                                    </el-popover>
                                </td>
                                <td class="no-ellipsis row-border" width="400px">
                                    <div class="" style="display: flex; flex-wrap: wrap;align-items: center;justify-content: flex-end;">
                                        <div v-if="container.analysis_payed[name]" class="has-icon" style="width: auto">
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
                                        <span style="display: block;width: 100%;margin:10px 0">{{ analysis }}</span> 
                                        <el-radio 
                                            v-if="container.analysis_name.length > 1"
                                            v-model="unUnited"
                                            :label="container.results[name]">&nbsp;</el-radio>  
                                        <div v-if="container.analysis_payed[name]"  style="width: auto;">
                                            <svg-icon
                                                name="delete"
                                                class="icon-small icon-red"
                                                @click="deleteAnalysis(container, index)" />
                                        </div>
                                    </div>
                                </td>
                                <td class="no-ellipsis"
                                    width="280px"
                                    :rowspan="container.analysis_name.length"
                                    colspan="1"
                                    v-if="name === 0">
                                        <el-input v-model="container.barcode"/>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </table>
            </el-col>
        </el-row>
        <el-row :gutter="20" class="mt-10">
            <el-col :span="24">
                <form-text
                    :entity="simular"
                    property="comment"
                    :placeholder="requiredCommentPlaceholder"
                    :required="isCommentRequired"
                    :label="__('Комментарий')"
                />
            </el-col>
         </el-row>
            <div class="form-footer text-right">
                 <el-button
                    v-if="canUnUnited"
                    @click="divide">
                    {{ __('Еще контейнер') }}
                </el-button>
                <el-button
                    @click="cancel">
                    {{ __('Отменить') }}
                </el-button>
                <el-button
                    type="primary"
                    :disabled="notFilledBarcodes"
                    @click="register">
                    {{ __('Зарегистрировать и сформировать заказ') }}
                </el-button>
            </div>
    </section>
</template>

<script>
import LaboratoryClientRepository from '@/repositories/analysis/laboratory/client';
import Container from '@/models/analysis/laboratory/container';
import BatchRequest from '@/services/batch-request';
import ContainerRepository from "@/repositories/analysis/laboratory/container";

export default {
    props: {
        model: Object,
        labaratoryAnalyses: {
            type: Array,
            default: () => [],
        },
    },
    beforeDestroy() {
        document.removeEventListener('keyup', this.onKeydown, false);
    },
    mounted() {
        document.addEventListener('keyup', this.onKeydown);
    },
    data() {
        return {
            containers: [],
            analysis: [],
            currentContainer: null,
            items: [],
            barcode: '',
            simular: {
                comment: '',
            },
            loading: true,
            showTable: true,
            batchRequest: new BatchRequest('/api/v1/analysis/laboratories/containers/batch'),
            unUnited: false,
            registerDisabled: true,
        }
    },
    computed: {
        notFilledBarcodes() {
            let noFilled = this.containers.find(container => {
                if (container.is_batch) {
                    let batchedAnalysisContainers = this.containers.filter(item => {
                        return _.difference(item.analysis_code, container.analysis_code).length === 0
                    });
                    return _.isVoid(batchedAnalysisContainers.find(item => _.isFilled(item.barcode)));
                } else {
                    return _.isVoid(container.barcode);
                }
            });
            return !_.isVoid(noFilled);
        },
        canUnUnited() {
            return this.unUnited > 0;
        },
        emptyFilters() {
            return _.isVoid(this.filters.clinicCode) &&
                   _.isVoid(this.filters.laboratoryCode) &&
                   _.isVoid(this.filters.description) &&
                   _.isVoid(this.filters.name);
        },
        emptySelected() {
            return this.selectedRows.length == 0;
        },
        isCommentRequired() {
            return !!this.labaratoryAnalyses.find(row => {
                return !!row.lab_analysis_notes;
            })
        },
        requiredCommentPlaceholder(){
            return  this.labaratoryAnalyses.map(item => {
                return item.lab_analysis_notes;
            }).join('\n');
        },
    },
    beforeMount() {
        this.getAnalyses();
    },
    methods: {
        getBiomaterialName(analysis, container) {
            let biomateral = analysis.biomaterials.find(item => {
                return  (item.container_id === container.container_id
                    && item.biomaterial_id === container.biomaterial_id)
            });
            if (biomateral.name) {
                return biomateral.name;
            }
            return '';
        },
        clearBarcode() {
            this.currentContainer.barcode = '';
        },
        onKeydown(e) {
            if (this.isNumberKey(e) && e.keyCode != 13) {
                this.barcode += e.key.replace(/\D/g, "");
            } else if(e.keyCode === 13) {
                this.loading = true;
                this.checkContainerSelected();
            }
        },
        isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode
            if (charCode != 43 && charCode > 31 && (charCode < 48 || charCode > 57) && (charCode < 106 && charCode > 95)) {
                 return false;
            }
            return true;
        },
         checkContainerSelected() {
            if (this.currentContainer === null) {
                this.$info(__('Пожалуйста выберите контейнер'));
                this.barcode = '';
                this.loading = false;
            } else if (this.currentContainer.barcode && this.currentContainer.barcode.length > 0) {
                this.$confirm(__('Внимание, данный контейнер уже отсканирован. Вы действительно хотите просканировать еще раз?'), () => {
                        this.readBarcode();
                    },  {
                        cancelled: ()=> {
                            this.loading = false;
                        }
                    })
            } else {
                this.readBarcode()
            }
        },
        notAvailableBarcodeWarning() {
            this.$info(__('Данный штрихкод уже используется'));
            this.barcode = '';
            this.loading = false;
        },
        readBarcode() {
            let repo = new ContainerRepository({filters: {barcode: this.barcode}});
            repo.fetch().then((response) => {
                if (response.rows.length > 0) {
                    this.notAvailableBarcodeWarning();
                } else {
                    let barcode = this.containers.find(item => {
                        return item.barcode === this.barcode;
                    });
                    if (barcode === undefined) {
                        this.setBarcode();
                    } else {
                        this.notAvailableBarcodeWarning();
                    }
                    this.loading = false;
                }
            });
        },
        setBarcode() {
            this.currentContainer.barcode = this.barcode;
            this.barcode = '';
        },
        
        notPayed(analysis) {
            let item = this.labaratoryAnalyses.find(item => item.lab_analysis_id === analysis.lab_analysis_id);
            return item.payed === 0;
        },
        cancel() {
            this.$emit('cancel');
        },
        skipping(row, status) {
            row.set('is_postponed', status);
        },
        findResultAttribute(item, attribute) {
            let result = this.labaratoryAnalyses.find(row => {
                return row.lab_analysis_id === item.lab_analysis_id;
            })

            return (result) ? result[attribute]: null;
        },
        castItems(rows) {
            rows.forEach(item => {
                item.set('patient_id', this.model.patient_id);
                item.set('result_id', this.findResultAttribute(item, 'id'));
            });
            return rows;
        },
        register() {
            if(this.isCommentRequired && (!this.simular.comment || this.simular.comment === "" )){
                this.$error(__('Введите комментарий'))
                return false;
            }
            this.loading = true;
            this.saveOrder(this.containers);
        },
        saveOrder(items) {
            this.batchRequest.reset();
            items.forEach(item => {
                if (_.isFilled(item.barcode)) {
                     this.batchRequest.create(this.createItem(item));
                }
               
            })
            this.batchRequest.submit().then(() => {
                this.$info(__('Биоматериал успешно зарегистрирован'));
                this.$emit('save', {items: items});
            }).catch((e) => {
                console.log(e);
                this.$warning(__('Что-то пошло не так'));
            });
            this.loading = false;
        },
        createItem(item) {
            return new Container({
                patient_id: this.model.patient_id,
                comment: this.simular.comment,
                ...item,
                });
        },
        getAnalyses() {
            let client = new LaboratoryClientRepository();
            client.fetch(this.getAnalysesFilter(), [{field: 'amount_min', direction: 'desc'}], ['containers','biomaterials'], 1, 50,'analysis').then(response => {
                this.items = this.castItems(response.rows);
                this.groupContainers();
                this.loading = false;
            });
        },
        getAnalysesFilter() {
            return  { id: this.labaratoryAnalyses.map(item => {
                return item.lab_analysis_id;
            })};
        },
        groupContainers() {
            let united = this.items.filter(item => item.unite);
            let others = this.items.filter(item => !item.unite);
            
            if (united) {
                var grouped = _.groupBy(united, function(i) {
                    return i.containers[0].container_id + '-' + i.containers[0].biomaterial_id
                })
                try {
                    do  {
                        Object.keys(grouped).forEach(item => {
                            grouped[item].forEach((container,index) => {
                                let measure = this.getContainerMeasure(container.containers);
                                if (container.measure != measure) {
                                    throw __('Ошибка единицы измерения анализа и контейнера. Проверьте единицу измерения в анализе и контейнере. ' + container.name);
                                }
                            
                                let suitableContainer = this.containers.find(elem => {
                                    return elem.container_id === container.containers[0].container_id &&
                                        elem.biomaterial_id === container.containers[0].biomaterial_id &&
                                        elem.measure === container.containers[0].measure &&
                                        (elem.amount + this.calcAmount(container) <= elem.volume)
                                });
                                
                                
                                if (!suitableContainer) {
                                    let maxAmount = _.maxBy(grouped[item], 'archive_amount');
                                    this.containers.push(...this.castContainerFromAnalysis(container, maxAmount.archive_amount));
                                } else {
                                    suitableContainer.analysis_name.push(container.value);
                                    suitableContainer.analysis_code.push(container.code);
                                    suitableContainer.analysis_payed.push(this.notPayed(container));
                                    suitableContainer.results.push(this.findResultAttribute(container, 'id'));
                                    suitableContainer.amount = suitableContainer.amount + this.calcAmount(container);

                                }
                                delete grouped[item][index];
                            });
                                
                        });
                    } while (grouped.length > 0);
                } catch(e) {
                    this.showTable = false;
                    this.$error(e);
                }
                

            }
           
            others.forEach(item => {
                this.containers.push(...this.castContainerFromAnalysis(item));
            });
           
 
        },
        getContainerMeasure(container) {
            return  container.map(el => {
                return el.measure;
            });
        },
        castContainerFromAnalysis(analysis, archived_amount = 0) {
            let containers = [];
          
            analysis.containers.forEach(container => {
                let item = {
                    amount_max: analysis.amount_max,
                    amount_min: analysis.amount_min,
                    amount: this.calcAmount(analysis) + archived_amount,
                    volume: container.volume,
                    archive_amount: analysis.archive_amount,
                    safety_amount: analysis.safety_amount,
                    analysis_code: [analysis.code],
                    analysis_name: [analysis.value],
                    analysis_payed: [this.notPayed(analysis)],
                    biomaterial_name: this.getBiomaterialName(analysis, container),
                    biomaterial_id: container.biomaterial_id,
                    container_name: container.name, 
                    container_id: container.container_id,
                    container_image: container.image_data,
                    barcode: '',
                    results: [this.findResultAttribute(analysis, 'id')],
                    handbook_measure: container.handbook_measure,
                    measure: container.measure,
                    is_batch: analysis.is_batch,
                }
                containers.push(item);
            });
            return containers;
        },
        calcAmount(analysis) {
            return (analysis.amount_min + analysis.safety_amount);
        },
        selectRow(row) {
            this.currentContainer = row;
        },
        deleteAnalysis(row, index) {
             this.$confirm(__('Вы дейстельно хотите удалить анализ?'), () => {
                let analysis = this.labaratoryAnalyses.find(item => {
                    return row.results.includes(item.id) 
                });
                this.$emit('deleteAnalysis', analysis);
                this.containers.splice(index, 1);
            });
        },
        divide() {
            if (this.unUnited) {
                let container = this.containers.find(container => {
                    return container.results.includes(this.unUnited);
                });
                if (container) {
                    let index = container.results.findIndex((item) => item === this.unUnited);
                    let row = {...container };
                    row.analysis_name = [container.analysis_name[index]];
                    row.analysis_payed = [container.analysis_payed[index]];
                    row.results = [container.results[index]];
                    row.analysis_code = [container.analysis_code[index]];
                    container.analysis_name.splice(index, 1);
                    container.analysis_payed.splice(index, 1);
                    container.results.splice(index, 1);
                    container.analysis_code.splice(index, 1);
                    this.containers.push(row);
                    this.unUnited = false;
                }
           }
        }
    }
}
</script>
