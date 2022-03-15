<template>
    <th 
        v-if="isHeader" 
        class="vuetable-th-component-checkbox">
        <label class="css-checkbox">
            <input type="checkbox"
                @change="toggleAllCheckbox($event)"
                :checked="isAllItemsInCurrentPageSelected()" />
            <span class="checkbox-appearance" />
        </label>
    </th>
    <td v-else
        class="vuetable-td-component-checkbox">
        <label class="css-checkbox">
            <input type="checkbox"
                @change="toggleCheckbox(rowData, $event)"
                :checked="isSelected(rowData)" />
            <span class="checkbox-appearance" />
        </label>
    </td>
</template>

<script>
import { VuetableFieldCheckbox } from 'vuetable-2';

export default {
    extends: VuetableFieldCheckbox,
    methods: {
        toggleCheckbox(dataItem, event) {
            this.vuetable.onCheckboxToggled(event.target.checked, this.rowField.name, dataItem);
            if (event.target.checked) {
                this.vuetable.fireEvent('checkbox-checked', dataItem);
            } else {
                this.vuetable.fireEvent('checkbox-unchecked', dataItem);
            }
        },
        toggleAllCheckbox(event) {
            this.vuetable.onCheckboxToggledAll(event.target.checked);
            if (event.target.checked) {
                this.vuetable.fireEvent('checkbox-checked-all');
            } else {
                this.vuetable.fireEvent('checkbox-unchecked-all');
            }
        },
    },
};
</script>