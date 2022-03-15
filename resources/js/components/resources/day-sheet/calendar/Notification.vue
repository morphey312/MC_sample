<template>
	<el-row class="pt-20 pr-20 pl-20 pb-10 time-sheet-notification bb-grey-sh3" v-if="hasNotification">
        <div>
            {{ __('Есть заполненый табель в клинике:') }}
            <span v-for="clinic in notifications">
                <clinic-popover :clinic="clinic" />
            </span>
        </div>
    </el-row>
</template>

<script>
import ClinicPopover from './ClinicPopover.vue';

export default {
	components: {
        ClinicPopover,
    },
    props: {
		notifications: {
			type: [Array, Object],	
		},
	},
    computed: {
    	hasNotification() {
	        return !_.isEmpty(this.notifications);
	    },	
    },
}	
</script>