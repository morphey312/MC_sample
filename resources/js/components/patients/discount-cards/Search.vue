<template>
     <div class="sections-wrapper">
        <section class="filter-inline">
            <card-filter
                ref="filter" 
                @changed="changeFilters"
                @cleared="clearFilters"
                @propose-disable="setDisableOwner"
             />
        </section>
        <section class="pt-0 pb-0">
            <form-row name="disable-owner">
                 <el-checkbox 
                    v-model="disableOwner" 
                    :label="__('Отключить выбранную карту у владельца')"
                 />
            </form-row>
        </section>
        <section class="grey-cap flex-content">
            <card-list
                v-if="!emptyFilters"
                ref="table"
                :filters="filters"
                @selection-changed="setActiveItem"
                @header-filter-updated="syncFilters"
                @loaded="refreshed">
                <div class="buttons" slot="buttons">
                    <el-button
                        @click="cancel">
                        {{ __('Отменить') }}
                    </el-button>
                    <el-button
                        :disabled="activeItem === null"
                        @click="select">
                        {{ __('Выбрать') }}
                    </el-button>
                </div>
            </card-list>
        </section>
     </div>
</template>

<script>
import CardFilter from './SearchFilter.vue';
import CardList from './SearchList.vue';
import ManageMixin from '@/mixins/manage';

export default {
    mixins: [
        ManageMixin,
    ],
    components: {
        CardFilter,
        CardList,
    },
    props: {
        skipList: Array,
        default: () => [],
    },
    data() {
        return {
            disableOwner: false,
        }
    },
    computed: {
        emptyFilters() {
            return _.isVoid(this.filters.number) && 
                   _.isVoid(this.filters.discount_card_type) &&
                   _.isVoid(this.filters.owner_last_name);
        },
    }, 
    methods: {
        changeFilters(filters) {
            this.filters = {...filters, skipId: this.skipList};
        },
        cancel() {
            this.$emit('cancel');
        },
        select() {
            this.activeItem.set('disableOwner', this.disableOwner);
            this.$emit('selected', this.activeItem);
        },
        setDisableOwner(val) {
            this.disableOwner = val;
        },
    }
}   
</script>