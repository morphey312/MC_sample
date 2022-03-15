class BatchRequest
{
    /**
     * Constructor
     * 
     * @param {string} url
     * @param {number} maxchunk
     */ 
    constructor(url, maxchunk = 200) {
        this._url = url;
        this._maxchunk = maxchunk;
        this.reset();
    }
    
    /**
     * Clear queues
     */ 
    reset() {
        this._create = [];
        this._update = [];
        this._delete = [];
        this._chain = null;
    }
    
    /**
     * Add model to save to request
     * 
     * @param {object} model
     */ 
    save(model) {
        if (model.isNew()) {
            this.create(model);
        } else {
            this.update(model);
        }
    }
    
    /**
     * Add model to create to request
     * 
     * @param {object} model
     */ 
    create(model) {
        if (this._create.length >= this._maxchunk) {
            this.chain().create(model);
        } else {
            this.add(this._create, model);
        }
    }
    
    /**
     * Add model to update to request
     * 
     * @param {object} model
     */ 
    update(model) {
        if (this._update.length >= this._maxchunk) {
            this.chain().update(model);
        } else {
            this.add(this._update, model);
        }
    }
    
    /**
     * Add model to delete to request
     * 
     * @param {object} model
     */ 
    delete(model) {
        if (this._delete.length >= this._maxchunk) {
            this.chain().delete(model);
        } else {
            this.add(this._delete, model);
        }
    }

    /**
     * Create next part of request
     * 
     * @return {BatchRequest}
     */
    chain() {
        if (this._chain === null) {
            this._chain = new BatchRequest(this._url, this._maxchunk);
        }
        return this._chain;
    }
    
    /**
     * Add model to request
     * 
     * @param {array} queue
     * @param {object} model
     */ 
    add(queue, model) {
        if (queue.indexOf(model) === -1) {
            queue.push(model);
        }
    }
    
    /**
     * Submit the request
     * 
     * @param {bool} skipValidation
     * 
     * @returns {Promise}
     */ 
    submit(skipValidation = false) {
        let validate = new Promise((resolve, reject) => {
            if (skipValidation) {
                resolve();
            } else {
                this.validate().then((invalid) => {
                    if (invalid.length === 0) {
                        resolve();
                    } else {
                        reject({invalid});
                    }
                });
            }
        });

        return validate.then(() => {
            return axios.post(this._url, this.buildBody())
                .then((response) => {
                    let results = this.processResponse(response.data);
                    if (this._chain === null) {
                        return results;
                    }
                    return this._chain.submit(true).then((chainResults) => {
                        return {
                            success: results.success.concat(chainResults.success),
                            failure: results.failure.concat(chainResults.failure),
                        }
                    });
                });
        });
    }
    
    /**
     * Validate the request
     * 
     * @returns {Promise}
     */ 
    validate() {
        let invalid = [];
        let tasks = [];
        _.concat(this._create, this._update).forEach((model) => {
            tasks.push(model.validate().then((errors) => {
                if (!_.isEmpty(errors)) {
                    invalid.push(model);
                }
            }));
        });
        return Promise.all(tasks).then(() => {
            if (this._chain === null) {
                return invalid;
            }
            return this._chain.validate().then((chainInvalid) => {
                return invalid.concat(chainInvalid);
            });
        });
    }
    
    /**
     * Build request body
     * 
     * @returns {object}
     */ 
    buildBody() {
        let body = {};
        if (this._create.length) {
            body.create = this._create.map((item) => item.getSaveData());
        }
        if (this._update.length) {
            body.update = this._update.map((item) => item.getSaveData());
        }
        if (this._delete.length) {
            body.delete = this._delete.map((item) => item.identifier());
        }
        return body;
    }
    
    /**
     * Process the response
     * 
     * @param {object} response
     * 
     * @returns {object}
     */ 
    processResponse(response) {
        let success = [];
        let failure = [];
        if (response.create !== undefined) {
            this.sync(this._create, response.create, success, failure, true);
        }
        if (response.update !== undefined) {
            this.sync(this._update, response.update, success, failure, true);
        }
        if (response.delete !== undefined) {
            this.sync(this._delete, response.delete, success, failure, false);
        }
        return {success, failure};
    }
    
    /**
     * Sync models
     * 
     * @param {array} destination
     * @param {array} source
     * @param {array} success
     * @param {array} failure
     * @param {bool} refill
     */ 
    sync(destination, source, success, failure, refill) {
        for (let i = 0; i < destination.length; i++) {
            let dest = destination[i];
            let src = source[i];
            if (src === undefined || src.error !== undefined) {
                failure.push(dest);
            } else {
                success.push(dest);
                if (refill) {
                    dest.update(src.data);
                }
            }
        }
    }
    
    /**
     * Check if create part of request is not empty
     * 
     * @returns {bool}
     */ 
    get hasCreates() {
        return this._create.length !== 0;
    }
    
    /**
     * Check if update part of request is not empty
     * 
     * @returns {bool}
     */ 
    get hasUpdates() {
        return this._update.length !== 0;
    }
    
    /**
     * Check if delete part of request is not empty
     * 
     * @returns {bool}
     */ 
    get hasDeletes() {
        return this._delete.length !== 0;
    }
    
    /**
     * Check if request is not empty
     * 
     * @returns {bool}
     */ 
    get isNotEmpty() {
        return this.hasCreates
            || this.hasUpdates
            || this.hasDeletes;
    }
}

export default BatchRequest;