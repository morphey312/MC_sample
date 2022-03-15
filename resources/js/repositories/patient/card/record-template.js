import BaseRepository from '@/repositories/base-repository';
import RecordTemplate from '@/models/patient/card/record-template';

class RecordTemplateRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/patients/cards/record-templates';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new RecordTemplate(row);
    }

    /**
     * Get template for particular specialization
     *
     * @param {array} specialization
     *
     * @returns {Promise}
     */
    getForSpecialization(specialization = null) {
        let filters = {};
        if (specialization) {
            filters = {
                or: [
                    _.onlyFilled({specialization}),
                    {is_fallback: 1},
                ],
            };
        } else {
            filters = {is_fallback: 1};
        }

        return this.fetch(filters, null, null, 1).then((result) => {
            if (result.rows.length > 1) {
                result.rows = result.rows.filter(row => row.is_fallback === false);
            }
            return result.rows.length === 0 ? null : result.rows[0];
        });
    }
}

export default RecordTemplateRepository;
