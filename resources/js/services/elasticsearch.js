const SCROLL_LIFETIME = '30s';
const HITS_SIZE = 1500;

class ElasticSearchClient
{
    /**
     * @param {String} cluster 
     */
    constructor(cluster = 'default') {
        this._endpoint = appConfig.elasticSearch.proxy_url;
        this._host = ((host) => {
            return (host.https || 'https') + '://' + host.host 
                + (host.port ? (':' + host.port) : '');
        })(appConfig.elasticSearch.hosts[cluster]);
    }

    /**
     * Perform search query
     *
     * @param {object} query
     *
     * @returns {Promise}
     */
    search(query) {
        return axios.get(this._endpoint, {
            params: {
                host: this._host,
                query: query,
            },
        });
    }

    /**
     * Request aggregations
     *
     * @deprecated 
     * 
     * @param {string} indexName
     * @param {object} query
     * @param {array} aggrGroups
     *
     * @returns {Promise}
     */
    getAggregations(indexName, query, aggrGroups = ['aggr_group']) {
        return this.search({
            index: indexName,
            body: this.stringify(query)
        }).then(result => {
            return this.performAggrResponse(result.data.body, result.data.body.aggregations, aggrGroups);
        });
    }

    /**
     * Pefrorm aggregation query
     * 
     * @param {string} indexName 
     * @param {object} query 
     * 
     * @returns {object}
     */
    aggregate(indexName, query) {
        return this.search({
            index: indexName,
            body: this.stringify({
                size: 0,
                ...query,
            }),
        }).then(result => {
            return result.data.body.aggregations;
        });
    }

    /**
     * Perform suggestion query
     * 
     * @param {String} index 
     * @param {String} query 
     * @param {Object} options 
     * @returns 
     */
    suggest(index, query, options) {
        return this.search({
            source: JSON.stringify({
                suggest: {
                    [index]: {
                        prefix: query,
                        completion: {
                            skip_duplicates: true,
                            fuzzy: {
                                fuzziness: 3,
                            },
                            size: 8,
                            ...options,
                        },
                    },
                },
            }),
            source_content_type: 'application/json',
        }).then((response) => {
            return _.get(response.data.body, 'suggest.' + index +'[0].options', []);
        });
    }

    /**
     * Get aggregation response
     *
     * @param {object} data
     * @param {array} aggrFields
     *
     * @returns {Promise}
     */
    performAggrResponse(data, aggregations, aggrGroups = ['aggr_group']) {
        let groups = {};

        if (data && aggregations) {
            aggrGroups.forEach(groupName => {
                let groupData = _.get(aggregations, groupName, {});
                let buckets = groupData.buckets || [];
                groups[groupName] = buckets;
            });
        }

        return Promise.resolve(groups);
    }

    /**
     * Request hits
     *
     * @param {object} query
     *
     * @returns {Promise}
     */
    getHits(indexName, query) {
        return this.search({
            index: indexName,
            body: this.stringify({
                size: HITS_SIZE,
                ...query
            }),
            scroll: SCROLL_LIFETIME,
        }).then(result => {
            return result.data;
        }).catch(error => {
            console.log(error)
        });
    }

    /**
     * Get json string
     *
     * @param {object} params
     *
     * @returns {string}
     */
    stringify(params = {}) {
        return JSON.stringify(params);
    }

    /**
     * Get config index name
     *
     * @param {string} key
     *
     * @returns {string}
     */
    getIndexName(key) {
        let indices = _.get(window, 'appConfig.elasticSearch.indices', null);
        return indices ? indices[key] : '';
    }
}

export default ElasticSearchClient;
