<template>
    <section>
        <el-row :gutter="20">
            <el-col :span="24">
                <form-row
                    name="active-clinic"
                    :label="__('Выберите номер карты (архив)')">
                    <el-select v-model="model.id">
                        <el-option
                            v-for="card in cards"
                            :key="card.id"
                            :value="card.id"
                            :label="card.number"
                        />
                    </el-select>
                </form-row>
            </el-col>
            <el-col :span="24">
                <form-row
                    name="protocol_file"
                    :label="__('Выберите файл')">
                    <form-upload
                        :key="model.id"
                        ref="attachments"
                        :on-preview="preview"
                        :entity="model"
                        :multiple="true"
                        property="attachments"
                    />
                </form-row>
            </el-col>
        </el-row>
        <div class="form-footer text-right">
            <el-button @click="cancel">
                {{ __('Отменить') }}
            </el-button>
            <el-button
                type="primary"
                @click="create">
                {{ __('Сохранить') }}
            </el-button>
        </div>
    </section>
</template>
<script>

import ArchivedCard from '@/models/patient/card/archived-card';
import FileActionMixin from '@/mixins/file-action';

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        patient: Object,
    },
    data() {
        return {
            cards: [],
            model: null,
            activeCard: null
        };
    },
    watch: {
        ['model.id'](val) {
            if(val){
                this.activeCard = this.cards.find((card) => card.id === val);
                this.model.set('attachments', this.activeCard.attachments);
                this.model.set('attachments_data', this.activeCard.attachments_data);
            }
        },
    },
    beforeMount() {
        this.init();
    },
    methods: {
        init() {
            this.model = new ArchivedCard();
            this.cards = this.patient.getArchivedCards();
        },
        cancel() {
            this.$emit('cancel');
        },
        preview(file){
            this.view(file.response.url, file.name)
        },
        create() {
            this.model.save().then((response) => {
                this.$info(__('Карта успешно сохранена'));
                this.$emit('saved', this.model);
            }).catch((e) => {
                this.$displayErrors(e);
            });
        },
    },

}
</script>
