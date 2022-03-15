<template>
    <page
        :title="__('Лаборатории')"
        type="flex">
        <section class="shrinkable">
            <laboratory-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <el-button
                        v-if="$canCreate('laboratories')"
                        @click="create" >
                        {{ __('Добавить') }}
                    </el-button>
                    <el-button
                        v-if="$canUpdate('laboratories')"
                        :disabled="activeItem === null || !$canManage('laboratories.update', activeItemClinicsIds)"
                        @click="edit" >
                        {{ __('Редактировать') }}
                    </el-button>
                    <el-button
                        v-if="$canDelete('laboratories')"
                        :disabled="activeItem === null || !$canManage('laboratories.delete', activeItemClinicsIds)"
                        @click="remove">
                        {{ __('Удалить') }}
                    </el-button>
                </div>
            </laboratory-list>
        </section>
    </page>
</template>

<script>
import LaboratoryList from './laboratories/List.vue';
import FormCreate from './laboratories/FormCreate.vue';
import FormEdit from './laboratories/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        LaboratoryList,
    },
    computed: {
        activeItemClinicsIds() {
            return this.activeItem.clinics.map(({clinic_id}) => clinic_id)
        }
    },
    methods: {
        getModalOptions() {
            return {
                createForm: FormCreate,
                editForm: FormEdit,
                createHeader: __('Добавить лабораторию'),
                editHeader: __('Редактировать лабораторию'),
                width: '700px',
            };
        },
        getMessages() {
            return {
                deleteConfirmation: __('Вы уверены, что хотите удалить эту лабораторию?'),
                deleted: __('Лаборатория успешно удалена'),
                created: __('Лаборатория успешно добавлена'),
                updated: __('Лаборатория успешно обновлена'),
            };
        }
    }
}
</script>
