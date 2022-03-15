import fileLoader from '@/services/file-loader';

const WORKER_URL = '/vendor/pdfjs/pdf.worker.js';
const pdfjsLib = window['pdfjs-dist/build/pdf'];
if (pdfjsLib !== undefined) {
    pdfjsLib.GlobalWorkerOptions.workerSrc = WORKER_URL;
}

let SCALE = 1;

class PdfView {
    /**
     * Constructor
     *
     * @param file
     * @param container
     */

    constructor(file, container, url = null) {
        if (pdfjsLib === undefined) {
            throw 'PDFjs library not found!';
        }
        this.docWindow = null;
        this._document = null;
        this._container = container;
        this._ready = false;

        pdfjsLib.getDocument(file.blob).promise.then((doc) => {
            this._document = doc;
            this.renderDocument().then(() => {
                this._ready = true;
                if (url !== null) {
                    fileLoader.revoke(url);
                }
            });
        });
    }

    /**
     * Render the document
     */
    renderDocument() {
        let promise = Promise.resolve();
        let doc = this._document;
        let container = this._container;
        this._ready = false;

        container.innerHTML = "";

        for (let i = 1; i <= doc.numPages; i++) {
            promise = promise.then(function (pageNum) {
                return doc.getPage(pageNum).then((pdfPage) => {
                    let pdfPageView = new pdfjsViewer.PDFPageView({
                        container: container,
                        id: pageNum,
                        scale: SCALE,
                        defaultViewport: pdfPage.getViewport({
                            scale: SCALE,
                        })
                    });

                    pdfPageView.setPdfPage(pdfPage);
                    return pdfPageView.draw();
                });
            }.bind(null, i));
        }

        return promise;
    }

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
    }

    print(data) {
        let docWindow = this.getDocWindow();
        docWindow.src = window.URL.createObjectURL(data.blobData);

        setTimeout(() => {
            docWindow.contentWindow.print();
        }, 100);
    }

    /**
     * Check if the document is ready
     *
     * @returns {bool}
     */
    get ready() {
        return this._ready;
    }

    zoomIn(amount){
        if(SCALE + amount < 1.5){
            SCALE += amount;
            this.renderDocument();
        }
    }

    zoomOut(amount){
        if(SCALE - amount > 0.1){
            SCALE -= amount;
            this.renderDocument();
        }
    }
}

export default PdfView;
