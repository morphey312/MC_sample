<template>
    <section>
        <cards-list
            v-if="patient !== null"
            ref="table"
            :patient="patient"
            @loaded="refreshed"
            @selection-changed="setActiveItem"
        />
        <div class="mt-10">
            <el-button
                v-if="$can('patient-discount-cards.create')"
                :disabled="patient === null || patient.loading"
                @click="create">
                {{ __('Добавить карту') }}
            </el-button>
            <el-button
                v-if="$can('patient-discount-cards.update')"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="edit">
                {{ __('Редактировать') }}
            </el-button>
            <el-button
                v-if="$can('patient-discount-cards.delete')"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="remove">
                {{ __('Удалить') }}
            </el-button>
            <el-button
                v-if="$can('patient-discount-cards.transfer')"
                :disabled="patient === null || patient.loading"
                @click="transfer">
                {{ __('Передать карту другого пациента') }}
            </el-button>
            <el-button
                v-if="$can('patient-discount-cards.operations')"
                :disabled="patient === null || patient.loading || activeItem === null"
                @click="operations">
                {{ __('Операции') }}
            </el-button>
        </div>
    </section>
</template>

<script>
import CardsList from '@/components/patients/discount-cards/List.vue';
import FormCreate from '@/components/patients/discount-cards/FormCreate.vue';
import FormEdit from '@/components/patients/discount-cards/FormEdit.vue';
import ManageMixin from '@/mixins/manage';
import Search from '@/components/patients/discount-cards/Search.vue';
import Operations from '@/components/patients/discount-cards/operations/Operations.vue';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CardsList,
        Operations
    },
    props: {
        patient: Object,
    },
    methods: {
        transfer() {
            this.$modalComponent(Search, {
                skipList: this.getSkipCards(),
                }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, card) => {
                    dialog.close();
                    card.addHolder(this.patient.id);
                    this.$emit('add-card', card);
                },
                }, {
                    header: __('Передача дисконтной карты'),
                    width: '800px',
                }
            );
        },
        getSkipCards() {
            let list = [];
            this.patient.issued_discount_cards.forEach(card => list.push(card.id));
            return list;
        },
        getModalOptions() {
            return {
                createForm: FormCreate,
                createProps: {
                    patient: this.patient,
                },
                editForm: FormEdit,
                editProps: {
                    patient: this.patient,
                    item: this.activeItem,
                },
                createHeader: __('Добавить карту'),
                editHeader: __('Изменить карту'),
                width: '700px',
            };
        },
        onCreated(card) {
            this.$emit('add-card', card);
        },
        onUpdated(card) {
            this.$emit('update-card', card);
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту карту?'),
                deleted: __('Карта была успешно удалена'),
                created: __('Карта была успешно добавлена'),
                updated: __('Карта была успешно обновлена'),
            };
        },
        remove() {
            this.$confirm(__('Вы уверены, что хотите удалить эту карту?'), () => {
                if(this.activeItem.isNew()) {
                    return this.onDelete();
                }

                return this.getIsDeleteable().then((response) => {
                    if (response == false) {
                        return this.$error(__('Карта не может быть удалена, есть связанная запись'));
                    }
                    this.onDelete();
                });
            });
        },
        onDelete() {
            this.$emit('delete-card', this.activeItem);
            this.setActiveItem([]);
        },
        getIsDeleteable() {
            return this.activeItem.isDeleteable().then((response) => {
                return Promise.resolve(true);
            }).catch((e) => {
                return Promise.resolve(false);
            });
        },
        operations() {
            this.$modalComponent(Operations, {
                card: this.activeItem
            }, {
                cancel: (dialog) => {
                    dialog.close();
                },
                selected: (dialog, card) => {
                    dialog.close();

                },
            }, {
                header: __('Операции по дисконтной карте'),
                width: '800px',
            }
            );
        }
    },
}
</script>
