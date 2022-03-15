import BaseRequest from './base-request';
import { 
    required,
    ukrSpelling,
} from '@/services/validation';

class ArchiveRequest extends BaseRequest
{
    constructor(archive) {
        super(archive);
        this.addProp('date', () => archive.date, [required]);
        this.addProp('place', () => archive.place, [required, ukrSpelling]);
    }
}

export default ArchiveRequest;