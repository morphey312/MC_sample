<template>
  <div class="el-transfer table-row-transfer">
    <transfer-panel
      v-bind="$props"
      ref="leftPanel"
      :data="sourceData"
      :title="titles[0] || t('el.transfer.titles.0')"
      :default-checked="leftDefaultChecked"
      :placeholder="filterPlaceholder || t('el.transfer.filterPlaceholder')"
      @checked-change="onSourceCheckedChange">
      <slot name="left-footer"></slot>
    </transfer-panel>
    <div class="el-transfer__buttons">
      <el-button
        type="primary"
        :class="['el-transfer__button', hasButtonTexts ? 'is-with-texts' : '']"
        @click.native="addToLeft"
        :disabled="rightChecked.length === 0">
        <i class="el-icon-arrow-left"></i>
        <span v-if="buttonTexts[0] !== undefined">{{ buttonTexts[0] }}</span>
      </el-button>
      <el-button
        type="primary"
        :class="['el-transfer__button', hasButtonTexts ? 'is-with-texts' : '']"
        @click.native="addToRight"
        :disabled="leftChecked.length === 0">
        <span v-if="buttonTexts[1] !== undefined">{{ buttonTexts[1] }}</span>
        <i class="el-icon-arrow-right"></i>
      </el-button>
    </div>
    <custom-panel
      v-bind="$props"
      ref="rightPanel"
      :data="targetData"
      :title="titles[1] || t('el.transfer.titles.1')"
      :default-checked="rightDefaultChecked"
      :placeholder="filterPlaceholder || t('el.transfer.filterPlaceholder')"
      :target-list="targetList"
      :target-fields="targetFields"
      :additional-field-type="additionalFieldType"
      @checked-change="onTargetCheckedChange">
      <slot name="right-footer"></slot>
    </custom-panel>
  </div>
</template>

<script>
import {Transfer} from 'element-ui';
import CustomPanel from './Panel.vue';

export default {
    extends: Transfer,
    components: {
        CustomPanel
    },
    props: {
        targetList: {
            type: [Object, Array],
        },
        targetFields: Array,
        additionalFieldType: String,
    },
    methods: {
        addToLeft() {
            let currentValue = this.value.slice();
            this.rightChecked.forEach(item => {
                const index = currentValue.indexOf(item);
                if (index > -1) {
                    currentValue.splice(index, 1);
                    delete this.targetList[item];
                }
            });
            this.$emit('input', currentValue);
            this.$emit('change', currentValue, 'left', this.rightChecked);
        },

        addToRight() {
            let currentValue = this.value.slice();
            const itemsToBeMoved = [];
            const key = this.props.key;
            this.data.forEach(item => {
                const itemKey = item[key];
                if (
                    this.leftChecked.indexOf(itemKey) > -1 &&
                    this.value.indexOf(itemKey) === -1
                ) {
                    itemsToBeMoved.push(itemKey);
                    this.targetList[itemKey] = {};
                }
            });
            currentValue = this.targetOrder === 'unshift'
              ? itemsToBeMoved.concat(currentValue)
              : currentValue.concat(itemsToBeMoved);
            this.$emit('input', currentValue);
            this.$emit('change', currentValue, 'right', this.leftChecked);
        },
    },
}
</script>