<template>
	<page
        :title="__('Медикаменты')"
        type="flex">
        <template slot="header-addon">
            <div class="buttons">
                <toggle-link v-model="displayFilter">
                    <svg-icon name="filter-alt" class="icon-small icon-blue">
                        {{ __('Фильтр') }}
                    </svg-icon>
                </toggle-link>
            </div>
        </template>
        <drawer :open="displayFilter">
            <section class="grey filter">
                <medicine-filter
                    ref="filter"
                    :initial-state="filters"
                    @changed="changeFilters"
                    @cleared="clearFilters" />
            </section>
        </drawer>
        <section class="grey-cap shrinkable">
			<medicine-list
				ref="table"
                :filters="filters"
                @header-filter-updated="syncFilters"
                @loaded="refreshed" />
    	</section>
    </page>
</template>

<script>
import MedicineList from './medicines/List.vue';
import MedicineFilter from './medicines/Filter.vue';
import ManageMixin from '@/mixins/manage';

export default {
	mixins: [
		ManageMixin,
	],
	components: {
		MedicineList,
		MedicineFilter,
    },
    data(){
	    return {
            displayFilter: true,
        }
    },
    methods: {
        getDefaultFilters() {
            return {
                clinic: this.getLoggedUserClinics(),
            };
        },
    },
}
</script>
