const UPLOAD_ENDPOINT = '/api/v1/file-attachments';
const UPLOAD_NAME = 'file';

class FileLoader
{
    /**
     * Constructor
     */
    constructor() {
        this._files = {};
        this._loading = {};
    }

    /**
     * Upload an attachment
     *
     * @param {string} name
     * @param {object} fileData
     * @param {string} originalName
     * @param {object} ajaxOptions
     *
     * @returns {onject}
     */
    upload(fileData, originalName = 'Attachment', ajaxOptions = {}) {
        let source = axios.CancelToken.source();
        let data = new FormData();
        data.append(UPLOAD_NAME, fileData, originalName);

        let promise = axios.post(UPLOAD_ENDPOINT, data, {
            cancelToken: source.token,
            ...ajaxOptions,
        });

        return {
            abort: () => {
                source.cancel('Cancelled');
            },
            promise: () => promise,
        };
    }

    /**
     * Fetch file
     *
     * @param {string} url
     * @param {object} options
     *
     * @param onlyURL
     * @returns {string}
     */
    get(url, options = {}, onlyURL = true) {
        if (url in this._files) {
            return Promise.resolve(this._files[url]);
        }

        if (url in this._loading) {
            return this._loading[url];
        }

        return this._loading[url] = axios.get(url, {
            responseType: 'blob',
            ...options,
        }).then((response) => {
            delete this._loading[url];
            this._files[url] = window.URL.createObjectURL(response.data);
            if (onlyURL) {
                return this._files[url];
            }
            console.log('FileLoader ', response.data);    
            return {
                blob: this._files[url],
                size: response.data.size,
                type: response.data.type,
                blobData: response.data,
            };
        });
    }

    /**
     * Revoke a single file
     *
     * @param {string} url
     */
    revoke(url) {
        if (url in this._files) {
            window.URL.revokeObjectURL(this._files[url]);
            delete this._files[url];
        }
    }

    /**
     * Revoke all files
     */
    revokeAll() {
        Object.keys(this._files).forEach((url) => {
            this.revoke(url);
        });
    }

    /**
     * Create new independent instance
     *
     * @returns {FileLoader}
     */
    new() {
        return new FileLoader();
    }
}

export default new FileLoader();
export {FileLoader};
