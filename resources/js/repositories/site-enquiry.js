import BaseRepository from '@/repositories/base-repository';
import SiteEnquiry from '@/models/site-enquiry';

class SiteEnquiryRepository extends BaseRepository
{
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/site-enquiries';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new SiteEnquiry(row);
    }
}

export default SiteEnquiryRepository;