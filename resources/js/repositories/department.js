import BaseRepository from '@/repositories/base-repository';
import Department from '@/models/department';

class DepartmentRepository extends BaseRepository
{
    /**
     * Constructor
     */ 
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/departments';
    }
    
    /** 
     * @inheritdoc
     */
    transformRow(row) {
        return new Department(row);
    }
}

export default DepartmentRepository;