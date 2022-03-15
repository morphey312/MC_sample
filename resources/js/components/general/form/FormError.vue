<template>
	<div
        v-if="errors.length !== 0"
        class="error-messages">
        <p v-for="error in errors">
            {{ error }}
        </p>
        
    </div>
</template>

<script>
export default {
	props: {
		onError: {
			type: Function
		},
	},
	data() {
        return {
            errors: [],
        }
    },
	mounted() {
        this.$eventHub.$on('validationErrors', this.showErrors);
    },
    beforeDestroy() {
        this.$eventHub.$off('validationErrors', this.showErrors);
    },
    methods: {
    	showErrors(errors) {
            if(_.isEmpty(errors)){
                return;
            }

            if(_.isFunction(this.onError)) {
            	this.errors = this.onError(errors);
            } else {
                this.errors = errors;
            }
        },	
    }
}	
</script>