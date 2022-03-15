<template>
    <div v-loading="!link" style="min-height: 600px">
        <video width="100%" height="100%" controls v-if="link">
            <source :src="link" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</template>
<script>
import FileActionMixin from '@/mixins/file-action';

export default {
    mixins:[
        FileActionMixin
    ],
    props: {
        url: String,
        name: String
    },
    data() {
        return {
            link: null,
            loading: false,
            settings: {},
        }
    },
    mounted() {
        this.fileLoader.get(this.url).then((file_url) => {
            this.link = file_url;
        })
    },
    methods: {
        download(){
            let link = document.createElement('a');
            link.href = this.link;
            link.download = this.name;
            link.click();
        },
    }
}
</script>
