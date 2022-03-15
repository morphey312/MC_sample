<template>
    <el-popover
        placement="bottom"
        :title="__('Что означает цвет строки:')"
        width="275"
        trigger="click"
        popper-class="info-mark-prices">
        <div
            v-for="(tag, index) in tagList"
            :key="index"
            class="item-tag">
            <div v-if="tag.class" class="item-tag-mark" :class="tag.class" />
            <div v-else class="item-tag-mark">
                <svg-icon :name="tag.icon" class="icon-tiddly icon-blue" />
            </div>
            &ndash; <span :class="tag.textClass">{{ tag.text }}</span>
        </div>
        <svg-icon slot="reference" name="question-alt" class="icon-tiny icon-blue action-link" />
    </el-popover>
</template>

<script>
export default {
    data() {
        return {
            tagList: [
                {
                    text: __('Изменилась цена'),
                    class: 'yellow',
                    textClass: '',
                },
                {
                    text: __('Идет согласование'),
                    class: 'grey',
                    textClass: '',
                },
                {
                    text: __('Добавлена клиника'),
                    class: 'blue',
                    textClass: '',
                },
                {
                    text: __('Закрылся тариф'),
                    class: 'green',
                    textClass: 'line-through',
                },
            ],
        };
    },
}
</script>
