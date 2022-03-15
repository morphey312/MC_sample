<template>
	<div class="mr-20">
		<toggle-link v-model="displayFilter" slot="filter">
	        <svg-icon name="filter-alt" class="icon-small icon-blue">
	            {{ __('Фильтр') }}
	        </svg-icon>
	    </toggle-link>	
	</div>
</template>
<script>
export default {
	data() {
		return {
			displayFilter: true,
		}
	},
	mounted() {
		this.$watch('displayFilter', () => {
			this.$emit('toggleFilter', this.displayFilter);
		})
	},
}	
</script>