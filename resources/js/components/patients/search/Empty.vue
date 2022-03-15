<template>
	<section class="form-tab p-0">
		<div class="empty-results">
			<div class="empty-results-content">
				<svg-icon name="dismiss-alt" class="icon-tiny icon-grey" /> <br>
                {{ __('Контакт не найден.') }}<br>
                {{ __('Попробуйте изменить поиск или') }}<br>
                <slot />
            </div>
		</div>
	</section>
</template>
<script>
export default {
}	
</script>