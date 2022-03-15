<template>
    <section>
        <cards-list
            v-if="patient !== null"
            ref="table"
            :patient="patient"
            @loaded="refreshed"
            @selection-changed="setActiveItem" />
        <div class="mt-10">
            <el-button
                v-if="$can('patient-cards.create')"
                :disabled="patient === null || patient.loading"
                @click="create">
                {{ __('Добавить') }}
            </el-button>
            <el-button
                v-if="$can('patient-cards.delete')"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
        </div>
    </section>
</template>

<script>
import CardsList from '@/components/patients/cards/List.vue';
import CreateCard from '@/components/patients/cards/Create.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CardsList,
    },
    props: {
        patient: Object,
    },
    methods: {
        create() {
            this.$modalComponent(CreateCard, 
            {
                patient: this.patient,
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                created: (dialog, card) => {
                    dialog.close();
                    this.$emit('add-card', card);
                },
            }, {
                header: __('Добавить карту'),
                width: '500px',
            });
        },
        remove() {
            this.$confirm(__('Вы уверены, что хотите удалить эту карту?'), () => {
                if(this.activeItem.isNew()) {
                    return this.onDelete();
                }

                return this.getIsDeleteable().then((response) => {
                    if(response == false) {
                        return this.$error(__('Карта не может быть удалена, есть связанные сущности'));
                    }

                    this.onDelete();
                });
            });
        },
        onDelete() {
            this.$emit('delete-card', this.activeItem);
            this.setActiveItem([]);   
        },
        refreshed() {
            if (this.activeItem !== null) {
                this.getManageTable().updateSelection((item) => {
                    return item.clinic_id == this.activeItem.clinic_id &&    
                           item.specialization_id == this.activeItem.specialization_id;    
                });
            }
        },
        getIsDeleteable() {
            return  this.activeItem.isDeleteable().then((response) => {
                        return Promise.resolve(true);
                    }).catch((e) => {
                        return Promise.resolve(false);
                    });
        },
    },
};
</script>