<template>
    <iframe ref="iframe" height="auto" width="100%" frameborder="0"
            onload="this.style.height=(this.contentWindow.document.body.scrollHeight+20)+'px';"
    ></iframe>
</template>
<script>
import printer from '@/services/print';

export default {
    props: {
        record: Object,
    },
    data(){
        return {
            settings: {},
        }
    },
    mounted(){
        this.buildHtml();
    },
    beforeDestroy() {
        printer.clearDownloads();
    },
    methods: {
        buildHtml(){
            if(this.record.header || this.record.footer){
                let header = printer.getImage(this.record.header);
                let footer = printer.getImage(this.record.footer);

                return Promise.all([header, footer]).then((images) => {
                    this.settings.header = images[0].img;
                    this.settings.footer = images[1].img;

                    printer.overrideSettings(this.settings);

                    this.$refs.iframe.srcdoc = printer.print(this.record.html, true);
                })
            }else {
                this.$refs.iframe.srcdoc = this.record.html;
            }

        },
        print() {
            this.$refs.iframe.contentWindow.print();
        },
    }
}
</script>
