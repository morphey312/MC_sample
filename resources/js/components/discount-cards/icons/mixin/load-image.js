import fileLoader from '@/services/file-loader';

export default {
    data() {
        return {
            src: null,
        }
    },
    beforeDestroy() {
        fileLoader.revokeAll();
    },
    methods: {
        getImage() {
            fileLoader.get(this.attachment.url).then((blobUrl) => {
                this.src = blobUrl;
            });
        }
    },
}