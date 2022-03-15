import FileViewer from '@/components/general/FileViewer.vue';
import {FileLoader} from '@/services/file-loader';
import FileViewerHeader from '@/components/general/FileViewerHeader';
import PopupMenu from '@/components/general/PopupMenu.vue';
const validFormats = ['text/plain', 'application/pdf', 'image/jpeg', 'image/png'];

export default {
    created() {
        this.fileLoader = new FileLoader()
    },
    beforeDestroy() {
        this.fileLoader.revokeAll();
    },
    methods: {
        view(url, header = '', fileData = {}, enableAddon = true) {
            this.$modalComponent(FileViewer, {url, data: fileData},
                {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    print: (dialog, data) => {
                        if (this.printHandle){
                            this.printHandle(data);
                        }
                        if (this.afterPrint){
                            this.afterPrint();
                        }
                        dialog.$emit('print', data);
                    },
                    download: (dialog, data) => {
                        if (this.afterDownload){
                            this.afterDownload();
                        }
                        dialog.$emit('download', data);
                    }
                }, {
                    header,
                    width: '1100px',
                    ...(enableAddon ?
                        {
                            headerAddon: {
                                component: FileViewerHeader,
                                eventListeners: {
                                    print: (dialog) => {
                                        dialog.getTopComponent().print();
                                    },
                                    downloadFile: (dialog) => {
                                        dialog.getTopComponent().download();
                                    }
                                },
                            }
                        } : {}
                    ),
                });
        },
        download(url, name = 'file') {
            this.fileLoader.get(url).then((blobUrl) => {
                let link = document.createElement('a');
                link.href = blobUrl;
                link.download = name;
                link.click();
            });
        },
        isValidType(type) {
            return validFormats.indexOf(type) !== -1;
        },
        errorFileFormat() {
            return this.$error(__('Измените формат файла'));
        },
        selectAndView(files) {
            if (files.length === 1) {
                if (this.isValidType(files[0].type)) {
                    this.view(files[0].url, files[0].name);
                } else {
                    this.download(files[0].url, files[0].name);
                }
            } else if (files.length > 1) {
                this.$modalComponent(PopupMenu, {
                    options: files.map(file => ({title: file.name, data: file})),
                }, {
                    cancel: (dialog) => {
                        dialog.close();
                    },
                    select: (dialog, data) => {
                        dialog.close();
                        if (this.isValidType(data.type)) {
                            this.view(data.url, data.name);
                        } else {
                            this.download(data.url, data.name);
                        }
                    }
                }, {
                    header: __('Выберите файл'),
                    width: '400px',
                });
            } else {
                this.$warning(__('Нет файлов доступных для просмотра'));
            }
        }
    }
}
