import Vue from 'vue';
import Clinic from '@/models/clinic';
import fileLoader from '@/services/file-loader';
import CONSTANTS from '@/constants';

const blankLoader = fileLoader.new();

const DEFAULTS = {
    styles: `
        @page {
            size: A4;
        }
        header, footer {
            position: fixed;
            left: 0;
            right: 0;
            height: 100px;
            width: 100%;
            text-align: left;
            box-sizing: border-box;
        }
        .pdf-canvas header, .pdf-canvas footer {
            position: static;
        }
        header {
            top: 0;
            padding-bottom: 30px;
        }
        footer {
            bottom: 0;
        }
        header img, footer img {
            max-height: 100px;
        }
        html, body {
            width: 185mm;
            height: 280mm;
            padding: 0;
            margin: 0;
            font-family: Arial, sans-serif;
            font-weight: normal;
            color: #000;
        }
        body > table > thead div, body > table > tfoot div {
            height: 100px;
        }
        body.pdf-canvas > table > thead div, body.pdf-canvas > table > tfoot div {
            display: none;
        }
        table {
            width: 100%;
        }
        h2 {
            font-size: 18px;
            line-height: 22px;
            margin-bottom: 15px;
            font-weight: normal;
        }
        h3 {
            font-size: 14px;
            line-height: 17px;
            font-weight: normal;
        }
        .printable-table {
            border: 1px solid #BDBDBD;
            margin-bottom: 30px;
            font-weight: normal;
        }
        table.table {
            border-spacing: 0;
            font-size: 12px;
        }
        .table th,
        .table td {
            text-align: left;
            padding: 5px;
        }
        .table th.text-right, .table td.text-right {
            text-align: right;
        }
        .table th:not(:first-child),
        .table td:not(:first-child) {
            border-left: 1px solid #BDBDBD;
        }
        @media print {
            .table thead tr th,
            .table tbody tr:nth-child(even) td {
                background-color: #F2F2F2 !important;
                -webkit-print-color-adjust: exact;
            }
        }
        .card-record-section h2, .card-record-section h3 {
            margin-bottom: 15px;
        }
        .card-record-line {
            display: flex;
        }
        .card-record-field {
            display: flex;
            justify-content: stretch;
            flex-wrap: wrap;
            line-height: 16px;
            font-size: 14px;
            max-width: 100%;
        }
        .card-record-field + .card-record-field {
            margin-left: 5px;
        }
        .card-record-field.growable {
            flex: 1 1 auto;
        }
        .card-record-field .field-input {
            width: auto;
            flex: 1;
            min-width: 30px;
            margin: 6px 0;
        }
        .card-record-field .prefix, .card-record-field .suffix {
            flex: none;
            white-space: nowrap;
            font-weight: 500;
            display: inline-block;
            margin-top: 6px;
            margin-bottom: 6px;
        }
        .card-record-field .prefix {
            margin-right: 5px;
        }
        .border-bottom {
            border-bottom: 1px solid #BDBDBD;
        }
        .el-checkbox {
            color: #000;
            font-weight: 500;
            font-size: 12px;
            position: relative;
            display: inline-block;
            white-space: nowrap;
        }
        .card-record-field .el-checkbox {
            margin: 6px 10px 6px 0;
        }
        .el-checkbox__inner {
            position: relative;
            display: inline-block;
            box-sizing: border-box;
            width: 12px;
            height: 12px;
            border: 1px solid #4F4F4F;
        }
        .el-checkbox__input.is-checked .el-checkbox__inner::after {
            content: "\\2713";
            position: absolute;
            width: 8px;
            height: 18px;
            top: -4px;
            left: 0;
            font-size: 18px;
            box-sizing: content-box;
        }
        .el-checkbox__original {
            display: none;
        }
        .ds-input .input {
            margin: 0;
            line-height: 14px;
            border: 0;
            border-bottom: 1px solid #E0E0E0;
            width: 15px;
            text-align: center;
            font-size: 14px;
            font-weight: 800;
        }
        .card-record-field.field-right {
            margin-left: auto;
        }
        .mt-10 {
            margin-top: 10px;
        }
        .mt-30 {
            margin-top: 30px;
        }
        .uppercase {
            text-transform: uppercase;
        }
        .text-center {
            text-align: center;
        }
        .sign-wrapper {
            width: 200px;
            position: relative;
        }
        .sign {
            position: absolute;
            top: 10px;
            margin: 0 auto;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            border-top: 1px #000 dashed;
        }
    `,
    header: '',
    footer: '',
};

class Printer {
    /**
     * Constructor
     */
    constructor() {
        this.iframe = null;
        this.settings = DEFAULTS;
        this.cached_settings = {};
    }

    /**
     * Create irrame or get previously created
     *
     * @returns {HTMLIframeElement}
     */
    createIframe() {
        if(this.iframe === null){
            let iframe = document.createElement('iframe');
            iframe.style.width = '1px';
            iframe.style.height = '1px';
            iframe.style.position = 'absolute';
            iframe.style.top = '-100px';
            this.iframe = document.body.appendChild(iframe);
        }

        return this.iframe;
    }

    /**
     * Create iframe and get its window object
     *
     * @returns {Window}
     */
    getDocWindow() {
        let iframe = this.createIframe();
        return iframe.contentWindow;
    }

    /**
     * Add leading html to the document
     *
     * @param {HTMLDocument} doc
     */
    startDocument(doc) {
        doc.write('<html><head>');
        this.addStyles(doc);
        doc.write('</head><body>');
        if (this.settings.header !== '') {
            this.addHeader(doc);
        }
        doc.write('<table>');
        if (this.settings.header !== '') {
            doc.write('<thead><tr><td><div></div></td></tr></thead>');
        }
        if (this.settings.footer !== '') {
            doc.write('<tfoot><tr><td><div></div></td></tr></tfoot>');
        }
        doc.write('<tbody><tr><td><section>');
    }

    /**
     * Add trailing html to the document
     *
     * @param {HTMLDocument} doc
     */
    endDocument(doc) {
        doc.write('</section></td></tr></tbody></table>');
        if (this.settings.footer !== '') {
            this.addFooter(doc);
        }
        doc.write('</body></html>');
    }

    /**
     * Add CSS styles to the document
     *
     * @param {HTMLDocument} doc
     */
    addStyles(doc) {
        doc.write('<style>');
        doc.write(this.settings.styles);
        doc.write('</style>');
    }

    /**
     * Add header to the document
     *
     * @param {HTMLDocument} doc
     */
    addHeader(doc) {
        doc.write('<header>');
        doc.write(this.settings.header);
        doc.write('</header>');
    }

    /**
     * Add footer to the document
     *
     * @param {HTMLDocument} doc
     */
    addFooter(doc) {
        doc.write('<footer>');
        doc.write(this.settings.footer);
        doc.write('</footer>');
    }

    /**
     * Print html inside the print layout
     *
     * @param {string} data
     *
     * @param dryRun Wont actually print if true, will only return print HTML.
     * @returns {string}
     */
    print(data, dryRun = false) {
        let docWindow = this.getDocWindow();
        let doc = docWindow.document;
        doc.open();
        this.startDocument(doc);
        doc.write(data);
        this.endDocument(doc);
        doc.close();
        if (!dryRun) {
            setTimeout(() => {
                docWindow.print();
            }, 100);
        }
        return doc.documentElement.outerHTML;
    }

    /**
     * Print raw html
     *
     * @param {string} html
     */
    printRawHtml(html) {
        let iframe = this.createIframe();
        iframe.srcdoc = html;

        setTimeout(() => {
            iframe.contentWindow.print();
        }, 100);
    }

    /**
     * Render and print a component
     *
     *
     * @param {object} component
     * @param {object} propsData
     * @param {number} clinicId
     * @param {object} settingOverrides
     *
     * @returns {Promise}
     */
    printComponent(component, propsData = {}, clinicId = null, settingOverrides = null) {
        this.setDefaultSettings();
        this.overrideSettings(settingOverrides);

        return this.getClinicBlanks(clinicId).then(() => {
            let documentData = {
                ...(this.cached_settings[clinicId] || {})
            };
            let body = this.getComponentHtml(component, propsData);
            documentData.body = body;
            this.print(body);
            documentData.document = this.getDocWindow().document;
            return Promise.resolve(documentData);
        })
    }

    /**
     * Render the component and get its html
     *
     * @param {object} component
     * @param {object} propsData
     *
     * @returns {string}
     */
    getComponentHtml(component, propsData = {}) {
        let ComponentClass = Vue.extend(component);
        let instance = new ComponentClass({propsData});
        instance.$mount();
        let tempDiv = document.createElement('div');
        tempDiv.appendChild(instance.$el);
        let html = tempDiv.innerHTML;
        instance.$destroy();
        return html;
    }

    /**
     * Get clinic blanks
     *
     * @param {int} clinicId
     *
     * @returns Promise
     */
    getClinicBlanks(clinicId) {
        if (clinicId) {
            if (this.blanksLoaded(clinicId)) {
                let header = this.getImage(this.cached_settings[clinicId].header);
                let footer = this.getImage(this.cached_settings[clinicId].footer);
                return this.getClinicImages(clinicId, header, footer);
            } else {
                let clinic = new Clinic({id: clinicId});
                return clinic.getBlanks().then((blanks) => {
                    return this.getClinicSettings(blanks, clinicId);
                });
            }
        }
        return Promise.resolve();
    }

    /**
     * Set settings header and footer
     *
     * @param {array} blanks
     *
     * @returns Promise
     */
    getClinicSettings(blanks, clinicId) {
        let header = this.getImage(this.findBlankByType(blanks));
        let footer = this.getImage(this.findBlankByType(blanks, CONSTANTS.CLINIC.BLANK_TYPE.FOOTER));
        return this.getClinicImages(clinicId, header, footer);
    }

    /**
     * Get clinic header and footer image urls
     *
     * @param {int} clinicId
     * @param {Promise} header
     * @param {Promise} footer
     *
     * @returns Promise
     */
    getClinicImages(clinicId, header, footer) {
        return Promise.all([header, footer]).then((images) => {
            this.settings.header = images[0].img;
            this.settings.footer = images[1].img;
            this.cached_settings[clinicId] = {
                header: images[0].url,
                footer: images[1].url,
            };
        })
    }

    /**
     * Set default settings
     */
    setDefaultSettings() {
        this.settings = DEFAULTS;
    }

    /**
     * Override settings
     *
     * @param {object} overrides
     */
    overrideSettings(overrides = null) {
        if (overrides) {
            this.settings = {
                ...this.settings, ...overrides
            };
        }
    }

    /**
     * Get document settings
     *
     * @returns {Object}
     */
    get docSettings() {
        return this.settings;
    }

    /**
     * Get image string
     *
     * @param {string} url
     *
     * @returns Promise
     */
    getImage(url = null) {
        let img = '';
        if (url) {
            return blankLoader.get(url).then((blobUrl) => {
                img = `<img src="${blobUrl}" />`;
                return Promise.resolve({img, url});
            });
        }
        return Promise.resolve({img, url});
    }

    /**
     * Verify clinic blanks already loaded
     *
     * @returns bool
     */
    blanksLoaded(clinicId) {
        return !_.isEmpty(this.cached_settings[clinicId]);
    }

    /**
     * Get clinic blank by type
     *
     * @param {array} blanks
     * @param {string} type
     *
     * @returns {mixed}
     */
    findBlankByType(blanks, type = CONSTANTS.CLINIC.BLANK_TYPE.HEADER) {
        let blank = blanks.find(blank => blank.type == type);
        return (blank && blank.attachments.length != 0) ? blank.attachments[0].url : null;
    }

    /**
     * Clear downloaded header/footer
     */
    clearDownloads(){
        blankLoader.revokeAll();
    }

    /**
     *
     *  Destroy iframe
     */
    destroyIframe() {
        this.iframe = null;
    }

    /**
     * Create new printer instance
     *
     * @returns {Printer}
     */
    newPrinter() {
        return new Printer();
    }

    /**
     * Delete all iframe instances
     */
    deleteAllIframes() {
        setTimeout(() => {
            document.querySelectorAll('iframe').forEach(iframe => iframe.remove());
        }, 1000);
    }
}

export default new Printer();
