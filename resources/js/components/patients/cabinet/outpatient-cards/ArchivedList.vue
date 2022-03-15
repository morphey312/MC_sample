<template>
    <div style="max-width: 400px;">
        <manage-table
            ref="table"
            :fields="fields"
            :filters="filters"
            :scopes="scopes"
            :repository="repository"
            :enable-pagination="false">
            <template slot="spacer">
            </template>
            <template
                slot="attachments_data"
                slot-scope="props" >
                <div 
                    style="margin-top: 5px;"
                    v-for="archiveCard in props.rowData.attachments_data"
                    :key="archiveCard.id">
                    <a @click.prevent="view(archiveCard.url, __('Просмотр архивной карты {card}', {card: archiveCard.name}), props.rowData)">{{ archiveCard.name }}</a>
                </div>
            </template>
            <template slot="footer-top">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$can('patient-cabinet.create-archive-card')"
                        @click="addResearchResult">
                        {{ __('Добавить архивную карту') }}
                    </el-button>
                </div>
            </template>
        </manage-table>
    </div>
</template>
<script>
import FileActionMixin from '@/mixins/file-action';
import ClinicRepository from "@/repositories/clinic";
import AddArchivedCard from "@/components/patients/cabinet/outpatient-cards/modals/AddArchivedCardForm";
import ArchivedCardRepository from "@/repositories/patient/card/archived-card";

export default {
    mixins: [
        FileActionMixin,
    ],
    props: {
        patient: Object
    },
    data() {
        return {
            repository: new ArchivedCardRepository(),
            fields: [
                {
                    name: 'number',
                    title: __('Номер карты'),
                    width: '4%',
                },
                {
                    name: 'new_card.clinic_name',
                    title: __('Клиника'),
                    width: '8%',
                    filter: new ClinicRepository(),
                },
                {
                    name: 'attachments_data',
                    title: __('Файлы'),
                    dataClass: 'no-ellipsis',
                    width: '8%',
                },
            ],
            filters: {
                card_number: this.patient.cards
                    .map((card) => card.id)
            },
            scopes: ['new_card']
        };
    },
    methods: {
        getArchivedCards(){
            return this.patient.getArchivedCards();
        },
        addResearchResult() {
            this.$modalComponent(AddArchivedCard, {
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                saved: (dialog, card) => {
                    dialog.close();
                    this.$refs.table.refresh();
                    this.$emit('card-added', card);
                },
            },
            {
                header: __('Прикрепить архивную карту к пациенту: {name}', {name: this.patient.full_name}),
                width: '450px',
                customClass: 'padding-0',
            });
        },
        remove(card){
            this.$confirm(__('Вы уверены, что хотите удалить файл этой карты?'), () => {
                card.file_id = null;
                card.save().then(() => {
                    this.refresh();
                    this.$info(__('Файл карты успешно удалён'));
                });
            });
        },
        refresh() {
            this.$refs.table.refresh();
        },
    }
};
</script>
