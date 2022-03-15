<template>
    <el-popover
        placement="bottom"
        :title="__('Что означают метки в записях:')"
        width="275"
        trigger="click"
        popper-class="question-mark">
        <div
            v-for="(tag, index) in tagList"
            :key="index"
            class="item-tag">
            <div v-if="tag.class" class="item-tag-mark" :class="tag.class" />
            <div v-else class="item-tag-mark">
                <svg-icon :name="tag.icon" class="icon-tiddly icon-blue" />
            </div>
            &ndash; {{ tag.text }}
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
                    text: __('Первичный пациент'),
                    class: 'green',
                },
                {
                    text: __('Было обращение в СКК'),
                    class: 'red',
                },
                {
                    text: __('Черная метка'),
                    class: 'grey',
                },
                {
                    text: __('Есть страховка'),
                    class: 'blue',
                },
                {
                    text: __('Видеоконсультация'),
                    class: 'orange',
                },
                {
                    text: __('Оплату не брать'),
                    class: 'deep-blue-alt',
                },
                {
                    text: __('Внимание'),
                    class: 'red-alt',
                },
                {
                    text: __('Есть метки в записи'),
                    icon: 'snow-alt',
                },
            ],
        };
    },
}
</script>
