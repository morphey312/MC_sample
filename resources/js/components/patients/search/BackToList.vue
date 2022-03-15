<template>
	<div class="mr-20">
		<a href="#" @click.prevent="close">{{ __('Вернуться к списку пациентов') }}</a>
	</div>
</template>
<script>
export default {
	methods: {
		close() {
			this.$emit('close');
		}
	}
}	
</script>