<template>
    <div class="file-attachments">
        <template v-if="files.length !== 0">
            <div 
                v-for="file in files"
                class="file">
                <a 
                    href="#"
                    target="_blank"
                    :download="file.name"
                    @click="download($event, file)">
                    {{ file.name }}
                    <svg-icon 
                        v-if="file.status === undefined"
                        name="download" 
                        class="icon-blue" />
                    <svg-icon 
                        v-else
                        :style="{visibility: isDownloading(file) ? 'hidden' : 'visible'}"
                        name="check"
                        class="icon-tiny" />
                </a>
                <el-progress 
                    v-if="isDownloading(file)"
                    :percentage="file.progress"
                    :stroke-width="2">
                </el-progress>
            </div>
        </template>
        <template v-else>
            <span class="no-attachments">
                {{ __('Нет прикрепленных файлов') }}
            </span>
        </template>
    </div>
</template>

<script>
import fileLoader from '@/services/file-loader';

const DOWNLOADING = 'downloading';
const READY = 'ready';

export default {
    props: {
        attachments: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            files: this.attachments.map((item) => ({...item, progress: 0})),
        };
    },
    beforeDestroy() {
        fileLoader.revokeAll();
    },
    methods: {
        download(event, file) {
            let link = event.currentTarget;
            if (file.status === undefined) {
                file.status = DOWNLOADING;
                event.preventDefault();
                fileLoader.get(file.url, {
                    onDownloadProgress: (event) => {
                        if (event.total > 0) {
                            file.progress = parseInt(event.loaded / event.total * 100);
                        } else {
                            file.progress = 0;
                        }
                        this.$forceUpdate();
                    },
                }).then((blobUrl) => {
                    file.status = READY;
                    link.href = blobUrl;
                    link.click();
                    this.$forceUpdate(); 
                });
                this.$forceUpdate(); 
            } else if (file.status === DOWNLOADING) {
                event.preventDefault();
            }
        },
        isDownloading(file) {
            return file.status === DOWNLOADING;
        },
    }
}
</script>