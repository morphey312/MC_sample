<template>
    <div
        v-loading="loading"
        style="height:650px; position: relative;"
        class="sections-wrapper p-10 file-view__wrap">
        <div class="file-viewer-zoom__wrap">
            <svg-icon @click="zoomIn" name="zoom-in" class="cursor-pointer"></svg-icon>
            <svg-icon @click="zoomOut" name="zoom-out" class="cursor-pointer" style="margin-top: 10px;"></svg-icon>
        </div>
        <section
            v-show="pdf"
            ref="docContainer"
            class="light-grey pdf-container">
        </section>
        <div
            v-if="!pdf && file"
            class="img-container">
            <viewer :options="viewerOptions" :images="[file.blob]">
                <template slot-scope="scope">
                    <img v-for="src in scope.images" :src="src" :key="src" class="mw100-img">
                </template>
            </viewer>
        </div>
    </div>
</template>

<script>
import PdfView from '@/services/pdf-view';
import fileLoader from '@/services/file-loader';
import Viewer from "v-viewer/src/component.vue"

export default {
    components: {
        Viewer
    },
    props: {
        url: String,
        data: Object
    },
    data() {
        return {
            loading: true,
            pdf: null,
            file: null,
            docWindow: null,
            viewerOptions: { "inline": false, "button": false, "navbar": false,
                "title": false, "toolbar": false, "tooltip": false, "movable": true,
                "zoomable": true, "rotatable": true, "scalable": true, "transition": true,
                "fullscreen": true, "keyboard": true, "url": "data-source" }
        }
    },
    beforeDestroy() {
        fileLoader.revokeAll();
    },
    mounted() {
        this.getFile();
    },
    methods: {
        getFile(){
            fileLoader.get(this.url, {}, false).then((file) => {
                this.file = file;

                if (file.type === 'application/pdf') {
                    this.pdf = new PdfView(this.file, this.$refs.docContainer, this.url);
                }

                this.loading = false;
            })
        },
        cancel() {
            this.$emit('cancel');
        },
        print() {
            this.$emit('print', this.data);
            if (this.pdf) {
                this.pdf.print(this.file);
            } else {
                this.imgPrint();
            }
        },
        imgPrint() {
            let docWindow = this.getDocWindow();
            docWindow.src = window.URL.createObjectURL(this.file.blobData)

            setTimeout(() => {
                docWindow.contentWindow.print();
            }, 100);
        },
        getDocWindow() {
            if (this.docWindow === null) {
                let iframe = document.createElement('iframe');
                iframe.style.width = '1px';
                iframe.style.height = '1px';
                iframe.style.position = 'absolute';
                iframe.style.top = '-100px';
                document.body.appendChild(iframe);
                this.docWindow = iframe;
            }
            return this.docWindow;
        },
        download(title = 'file') {
            this.$emit('download', this.data);
            fileLoader.get(this.url).then((blobUrl) => {
                let link = document.createElement('a');
                link.href = blobUrl;
                link.download = title;
                link.click();
            });
        },
        zoomIn() {
            this.pdf.zoomIn(0.2);
        },
        zoomOut() {
            this.pdf.zoomOut(0.2);
        },
        getFileBlob() {
            return Promise.resolve(this.file.blobData);
        },
    },
};
</script>
