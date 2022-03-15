<template>
	<div class="buttons">
		<el-dropdown @command="handleCommand" class="action-link mr-10">
            <svg-icon name="plus-alt" class="icon-small icon-blue">
                {{ __('Добавить') }}<i class="el-icon-caret-bottom el-icon--right"></i>
            </svg-icon>
            <el-dropdown-menu slot="dropdown">
                <el-dropdown-item command="addDeposit">{{ __('Аванс') }}</el-dropdown-item>
				<el-dropdown-item command="addPrepayment">{{ __('Предоплату на услугу') }}</el-dropdown-item>
            </el-dropdown-menu>
        </el-dropdown>

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
    methods: {
		handleCommand(command) {
            return this[command]();
        },
        addDeposit() {
            this.$emit('addDeposit');
		},
		addPrepayment() {
			this.$emit('addPrepayment');
		},
    },
}	
</script>