<template>
    <page
        :title="__('Показатели операторов')"
        type="flex">
        <section class="shrinkable grey-cap">
            <norm-list
                ref="table"
                @selection-changed="setActiveItem"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <form-button 
                        v-if="$can('operator-bonuses.update')"
                        :disabled="activeItem === null"
                        :text="__('Редактировать')"
                        icon="edit"
                        @click="edit" />
                </div>
            </norm-list>
        </section>
    </page>
</template>

<script>
import NormList from './operator-bonuses/operators/List.vue';
import FormEdit from './operator-bonuses/operators/FormEdit.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        NormList,
    },
    methods: {
        getModalOptions() {
            return {
                editForm: FormEdit,
                editHeader: this.getEditHeader(),
                width: '800px',
            };
        },
        getMessages() {
            return {
                updated: __('Показатель успешно обновлен'),
            };
        },
        getEditHeader() {
            let header = __('Редактировать показатель оператору');
            return (this.activeItem == null) ? header : (`${header}  ${this.activeItem.full_name}`);
        },
    }
}
</script>