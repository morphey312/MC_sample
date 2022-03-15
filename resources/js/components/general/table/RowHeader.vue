<script>
import { VuetableRowHeader } from 'vuetable-2';
import FieldCheckbox from './FieldCheckbox.vue';

export default {
    extends: VuetableRowHeader,
    components: {
        'vuetable-field-checkbox': FieldCheckbox, 
    },
};
</script>