import BaseRepository from '@/repositories/base-repository';
import TreatmentCourse from '@/models/treatment-course';

class TreatmentCourseRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/treatment-courses';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new TreatmentCourse(row);
    }

    /**
     * Fetch patient journal list
     * 
     * @param {object} filters
     * @param {array} sort
     * @param {array} scopes
     * @param {number} page
     * @param {number} limit
     * 
     * @returns {Promise}
     */ 
    fetchJournalList(filters = null, sort = null, scopes, page = 1, limit = 50) {
        return this.fetchInternal(this.buildUrl('journal-list', {
            ...this.getFilters(filters), 
            ...this.getSort(sort), 
            ...this.getScopes(scopes), 
            page, 
            limit, 
        }));
    }
}

export default TreatmentCourseRepository;
