<template>
	<manage-table
        ref="table"
        :fields="fields"
        :repository="repository"
        :selectable-rows="true"
        @loaded="loaded"
        @selection-changed="selectionChanged" >
        <template slot="footer-top">
            <slot name="buttons" />
        </template>
    </manage-table>
</template>
<script>
export default {
	props: {
		repository: Object,
	},
	data() {
		return {
			fields: [
				{
                    name: 'name',
                    title: __('Назначение платежа'),
                    width: '25%',
                },
				{
                    name: 'discount_percent',
                    title: __('Размер скидки'),
                    width: '25%',
                },
                {
                    name: 'date_start',
                    title: __('Действует с'),
                    width: '25%',
                },
                {
                    name: 'date_end',
                    title: __('Действует до'),
                    width: '25%',
                },
			],
		}
	},
	methods: {
		loaded() {
            this.$emit('loaded');
        },
        selectionChanged(selection) {
            this.$emit('selection-changed', selection);
        },
	}
}	
</script>