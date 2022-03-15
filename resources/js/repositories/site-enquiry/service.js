import BaseRepository from '@/repositories/base-repository';
import EnquiryService from '@/models/site-enquiry/service';

class ServiceRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/site-enquiries/services';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new EnquiryService(row);
    }
}

export default ServiceRepository;