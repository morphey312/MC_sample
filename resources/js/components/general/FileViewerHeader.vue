<template>
    <div class="buttons">
        <a
            v-show="showDownload"
            href="#"
            @click.prevent="download"
        >
            <svg-icon
                name="download"
                class="icon-blue icon-small" >
                {{ __('Скачать') }}
            </svg-icon>
        </a>
        <a
            v-show="showPrint"
            href="#"
            @click.prevent="print">
            <svg-icon name="report" class="icon-small icon-blue">
                {{ __('Печатать') }}
            </svg-icon>
        </a>
    </div>
</template>
<script>
export default {
    props:{
        showPrint: {
            type: Boolean,
            default: true
        }  ,
        showDownload: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        print() {
            this.$emit('print');
        },
        download(){
            this.$emit('downloadFile');
        }
    }
}
</script>
