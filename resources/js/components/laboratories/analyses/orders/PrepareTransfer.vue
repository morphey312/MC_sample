<template>
    <div
        v-loading="loading">
        <el-row :gutter="20" class="mt-10">
            <el-col :span="6">
                <form-select
                    :entity="model"
                    property="reciever_id"
                    :options="externalLaboratories"
                    :label="__('Отправить в лабораторию')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="sender_comment"
                    :label="__('Примечание')"
                />
            </el-col>
            <el-col :span="6">
                <form-input
                    :entity="model"
                    property="barcode"
                    :label="__('Присвоить номер отправке')"
                />
            </el-col>
            <el-col :span="6">
                <form-select
                    :entity="model"
                    property="courier_id"
                    :options="laboratoryCouriers"
                    :label="__('Выберете курьера')"
                />
            </el-col>
        </el-row>
        <section class="flex-content" v-if="Object.keys(containers).length === 0" style="max-height:300px">
            <div class="empty-content-wrapper with-circle">
                <div class="empty-content top-10">
                    <div>
                        <div class="empty-content-img"></div>
                        <span class="">{{ __('Сканировать штрихкод ') }}</span>
                    </div>
                </div>
            </div>
        </section>
        <div v-for="(container, index) in containers"
            :key="index"
            class="transfer-details">
            <span>
                {{ __('{container_name}. Всего шт: {containers}',
                    {
                        containers: container.length,
                        container_name: container[0].container_name,
                    })
                }}
            </span>
            <el-row :gutter="20" class="mt-10">
                <el-col :span="4" style="border: 1px solid #BDBDBD;">
                    <div v-if="container[0].image_data">
                        <div class="image-container text-center" v-html="container[0].image_data"/>
                    </div>
                </el-col>
                <el-col :span="20">
                    <el-table
                        :data="container"
                        class="vuetable ui blue unstackable celled table fixed text-left"
                        style="width: 100%"
                        header-row-class-name="light-grey">
                        <el-table-column
                            :label="__('Штрих-код')"
                            prop="barcode"
                            width="175"
                            class-name="text-left" />
                        <el-table-column
                            :label="__('Дата и время забора')"
                            prop="created_at"
                            width="150"
                            class-name="text-left" />
                        <el-table-column
                            :label="__('Анализы')"
                            prop="analysis_names"
                            width="450"
                            class-name="text-left">
                            <template
                                slot-scope="scope"
                            >
                                <analysis-info :model="scope.row" >
                                    <template slot="column">
                                        {{ $formatter.listFormat(scope.row.analysis_names) }}
                                    </template>
                                </analysis-info>
                            </template>
                        </el-table-column>
                        <el-table-column
                            :label="__('Лаборатории')"
                            prop="laboratory_names"
                            :formatter="listFormatter"
                            width="75"
                            class-name="text-left" />
                            <el-table-column
                                prop="laboratory_names"
                                :formatter="listFormatter"
                                width="75"
                                class-name="text-left">
                                 <template slot-scope="scope">
                                    <svg-icon name="dismiss-alt" class="icon-small icon-red"  @click="remove(scope.row.id, scope.row.barcode)"/>
                                 </template>
                        </el-table-column>
                    </el-table>
                </el-col>
            </el-row>
            <br>
            <hr/>
        </div>
        <el-row class="dialog-footer text-right">
            <el-button
                class="primary-btn-outline no-margin-t"
                @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                class="primary-btn no-margin-t"
                type="primary"
                @click="save">
                {{ __('Сформировать отправку') }}
            </el-button>
        </el-row>
    </div>
</template>

<script>
import TransferSheet from '@/models/analysis/laboratory/transfer-sheet';
import LaboratoryRepository from '@/repositories/analysis/laboratory';
import LaboratoryClientRepository from '@/repositories/analysis/laboratory/client';
import ContainerRepository from '@/repositories/analysis/laboratory/container';
import CONSTANTS from '@/constants';
import AnalysisInfo from './AnalysisDetails.vue';

export default {
    components: {
        AnalysisInfo
    },
    data() {
        return {
            analyzes: [],
            loading: true,
            containers: {},
            scannedBarcodes: [],
            barcode: '',
            model: new TransferSheet(),
            client: new LaboratoryClientRepository(),
            laboratories: [],
            externalLaboratories: [],
            laboratoryCouriers: [],
        }
    },
    watch: {
        ['model.reciever_id'](val) {
            this.model.courier_id = null;
            let externalLaboratory = this.externalLaboratories.find(lab => {
                return lab.id === val;
            });
            this.model.reciever_name = externalLaboratory ? externalLaboratory.value : null;
            let laboratory = this.laboratories.find(lab => {
                return lab.external_id === val;
            });
            this.model.laboratory_id = laboratory ? laboratory.id : null;
            if (!_.isNull(val)) {
                this.getCouriers(val);
            }
        },
        ['model.courier_id'](val) {
            let laboratoryCourier = this.laboratoryCouriers.find(courier => {
                return courier.id === val;
            });
            this.model.courier_name = laboratoryCourier ? laboratoryCourier.value : null;
        },
        ['model.barcode']() {
            this.barcode = '';
        },
    },
    beforeDestroy() {
        document.removeEventListener('keyup', this.onKeydown, false);
    },
    mounted() {
        document.addEventListener('keyup', this.onKeydown);
        this.getLaboratories();
    },
    methods: {
        remove(id, barcode) {
            this.$confirm(__('Вы уверены что хотите удалить контейнер?'), () => {
                let containerIndex = this.model.containers.findIndex(elem => {
                    return elem == id;
                })
                let scannedIndex = this.scannedBarcodes.findIndex(item => item === barcode);
                 if (containerIndex != -1) {
                    this.scannedBarcodes.splice(scannedIndex, 1);
                }
                if (containerIndex != -1) {
                    this.model.containers.splice(containerIndex, 1);
                }
             
                Object.keys(this.containers).forEach(container => {
                    let index = this.containers[container].findIndex(item => {
                        return item.id == id;
                    });
                    if (index != -1) {
                        this.containers[container].splice(index, 1);
                    }
                   
                    if (this.containers[container].length === 0) {
                        this.$delete(this.containers, container)
                    }
                });
           
            });
        },
        getCouriers(val) {
            this.client.fetchList({laboratory_id: val, status: CONSTANTS.EMPLOYEE.STATUSES.WORKING, is_courier: true}, null, null, 'employees').then(response => {
                if (response && response.data) {
                    this.laboratoryCouriers = response.data;
                }
            });
        },
        listFormatter (row, column, cellValue, index) {
            return this.$formatter.listFormat(cellValue)
        },
        onKeydown(e) {
            if (this.isNumberKey(e) && e.keyCode != 13) {
                this.barcode += e.key;
            } else if(e.keyCode === 13) {
                if (document.activeElement.className == 'el-input__inner') {
                      this.barcode = '';
                } else {
                    this.fetchContainer(this.barcode);
                }

            }
        },
        isNumberKey(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode
            if (charCode != 43 && charCode > 31 && (charCode < 48 || charCode > 57))
                return false;
            return true;
        },
        fetchContainer(barcode) {
            if (this.model.laboratory_id === null) {
                this.barcode = '';
                return this.$warning(__('Выберете лабораторию получателя'));
            }

            if (this.scannedBarcodes.includes(barcode)) {
                this.barcode = '';
                return this.$warning(__('Данный штрих код уже был отсканирован'))
            }
            let repo = new ContainerRepository({
                 filters: {barcode: barcode, has_transfer_id: false}, 
                 scopes: ['results','patient']
                });
            repo.fetch().then((response) => {
                if (response.rows.length > 0) {
                    this.loading = true;
                    this.addContainer(response.rows[0]);
                } else {
                    this.$info(__('Данный штрихкод не зарегистрирован в системе или был уже использован'));
                }
            });
            this.clearBarcode();

        },
        clearBarcode() {
            this.barcode = '';
        },
        addContainer(container) {
            //need check if include laboratories in current reciever ID
         
            this.client.fetch({
                id: container.container_id
                }, null, ['image_data'], 1, 1,'containers').then(response => {
                    if (_.isEmpty(this.containers)) {
                        this.setClinic(container);
                    }
                    if (this.containers[container.container_id]) {
                        this.containers[container.container_id].push(this.castRow(container, response.rows[0]));
                    } else {
                        this.containers[container.container_id] = [this.castRow(container, response.rows[0])];
                    }
                    this.model.containers.push(container.id);
                    this.scannedBarcodes.push(container.barcode)
                    
                    this.loading = false;
            });

        },
        castRow(container, data) {
            return {
                id: container.id,
                barcode: container.barcode,
                created_at: container.created_at,
                container_name: data.name,
                analysis_names: container.results.map(result => result.analysis_names),
                laboratory_names: container.results.map(result => result.laboratory_names),
                image_data: data.image_data,
            };
        },
        setClinic(container) {
            if (container.results[0].clinic_ids) {
                this.model.set('clinic_id', container.results[0].clinic_ids);
            }
            
        },
        addContainerToOrder(container) {
            if (!this.analyzes.find(analyses => analyses.lab_analysis_id === container.lab_analysis_id)) {
                this.client.fetch({id: container.lab_analysis_id}, null, null, 1, 50,'analysis').then(response => {
                    this.analyzes.push(response.rows[0]);
                    this.$nextTick(() => {
                        this.checkReciever(container);
                    });
                });
            } else {
                this.checkReciever(container);
            }
        },
        checkReciever(container) {
            let analysis = this.analyzes.find(analyses => analyses.lab_analysis_id === container.lab_analysis_id)

            if (!analysis.laboratory_ids.includes(this.model.reciever_id)) {
                this.$confirm(__('Внимание, данный биоматериал не предназначен для данного получателя. Вы уверены что хотите добавить его?'), () => {
                        this.groupAnalyses(container, analysis)
                    }
                );
            } else {
                this.groupAnalyses(container, analysis)
            }
        },
        groupAnalyses(container, analysis) {
            this.model.containers.push(container)
            this.setClinic(container)

            let row = {
                laboratory_names: analysis.laboratory_names,
                ...container.attributes,
            }

            if (_.isArray(this.containers[row.container_id])) {
                this.containers[row.container_id].push(row);
            } else {
                this.containers[row.container_id] = [row];
            }
            this.scannedBarcodes.push(row.barcode)
            this.loading = false;
        },
        boolFormatter(value) {
            return this.$formatter.boolToString(value, '<span class="check-yes" />');
        },
        getLaboratories() {
            let getLaboratories = new LaboratoryRepository();
            getLaboratories.fetchList({has_external: true}).then(response => {
                this.laboratories = response;
                let externalKeys = this.laboratories.map(laboratory => {
                    return laboratory.external_id;
                });
                this.getExternalLaboratories(externalKeys);
            });
        },
        getExternalLaboratories(keys) {
            this.client.fetchList({id: keys}, null, null, 'laboratories').then(response => {
                if (response && response.data) {
                    this.externalLaboratories = response.data;
                    this.loading = false;
                }
            });
        },
        cancel() {
            this.$emit('close');
        },
        save() {
            this.$confirm(__('Вы уверены, что хотите отправить в лабораторию?'), () => {
                this.$emit('confirm', this.model);
            });
        },
        getDate(date) {
            return this.$formatter.dateFormat(date);
        },
    },
}
</script>

