import BaseModel from '@/models/base-model';
import CONSTANTS from '@/constants';
import Employee from '@/models/employee';
import Workspace from '@/models/workspace';

/**
 * Owner model
 */
class Owner extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
        }
    }

    /** 
     * @inheritdoc
     */
    routes() {
        return {
            fetchEmployee: '/api/v1/employees/{id}',
            fetchWokspace: '/api/v1/workspaces/{id}',
        }
    }

    /**
     * @inheritdoc
     */
    options() {
        return {
            methods: {
                fetchOwner: 'GET',
            }
        }
    }

    fetchOwner(owner, scopes = []) {
        let route; 
      
        if(owner === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE) {
            route = this.getRoute('fetchEmployee');
        } else if(owner === CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE) {
            route = this.getRoute('fetchWokspace');
        } else {
            throw 'Unrecognized owner type';
        }

        let method = this.getOption('methods.fetchOwner');
        let params = this.getRouteParameters();
        let url    = this.getUrlWithQueryParams(route, params, null, scopes);
        let data;
    
        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(this.castOwnerToModel(owner, response.response.data));
        });
    }

    castOwnerToModel(owner, data) {
        if(owner === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE) {
            return new Employee(data);
        } else if(owner === CONSTANTS.DAY_SHEET.OWNER_TYPES.WORKSPACE) {
            return new Workspace(data);
        }

        return {};
    }
}

export default Owner;