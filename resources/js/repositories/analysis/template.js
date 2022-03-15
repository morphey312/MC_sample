import BaseRepository from '@/repositories/base-repository';
import AnalysisTemplate from '@/models/analysis/template';

class AnalysisTemplateRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/analyses/templates';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new AnalysisTemplate(row);
    }
}

export default AnalysisTemplateRepository;