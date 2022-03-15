import BaseRepository from '@/repositories/base-repository';
import ProtocolTemplate from '@/models/patient/card/protocol-template';

class ProtocolTemplateRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super({
            sort: [{direction: 'asc', field: 'name'}],
            ...options,
        });
        this.endpoint = '/api/v1/patients/cards/protocol-templates';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new ProtocolTemplate(row);
    }
}

export default ProtocolTemplateRepository;