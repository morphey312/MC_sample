<template>
    <div class="tag-list">
        <div class="item-tag" v-if="showMore && !hovered">
            <svg-icon name="snow-alt" class="icon-tiddly icon-blue" />
        </div>
        <template v-else-if="(showMore && hovered) || !showMore">
            <div v-for="(tag, index) in tags" class="item-tag" :key="index">
                <el-popover
                    v-if="$store.state.user.isDoctor"
                    placement="right"
                    min-width="150px"
                    trigger="hover"
                    :open-delay="1000">
                    {{ tagInfo[tag] }}
                    <div class="item-tag-mark" :class="tag" slot="reference"/>
                </el-popover>
                <div v-else class="item-tag-mark" :class="tag" slot="reference"/>
            </div>
        </template>
    </div>
</template>

<script>
export default {
    props: {
        tags: {
            type: Array,
        },
        totalCells: {
            type: [String, Number],
        },
        hovered: {
            type: Boolean,
        },
        patient: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            tagInfo: {
                green: __('Первичный пациент'),
                red: __('Обращение в СКК:') + ' ' + (this.patient.skk_comment ? this.patient.skk_comment : ''),
                grey: __('Черная метка:') + ' ' + (this.patient.black_mark_comment ? this.patient.black_mark_comment : ''),
                blue: __('Есть страховка'),
                'deep-blue-alt': __('Оплату не брать'),
                'red-alt': __('Внимание:') + ' ' + (this.patient.attention_comment ? this.patient.attention_comment : ''),
                orange: __('Видеоконсультация'),
            },
        };
    },
    computed: {
        showMore() {
            let tagLen = this.tags.length;

            if (this.totalCells == 1 && tagLen > 1) {
                return true;
            }

            if (this.totalCells == 2 && tagLen > 3) {
                return true;
            }
            return false;
        },
    }
}
</script>
