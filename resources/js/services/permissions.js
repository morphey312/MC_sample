import Loadable from '@/services/loadable';

class Permissions extends Loadable
{
    /**
     * Constructor
     */ 
    constructor() {
        super();
        this._endpoint = '/api/v1/permissions/user';
    }
    
    /**
     * Check if current permissions allow perform the action
     * 
     * @param {string} action
     * 
     * @returns {bool}
     */ 
    can(action) {
        return this._data !== null && this._data.indexOf(action) !== -1;
    }
    
    /**
     * Check if current permissions allow perform some of the actions
     * 
     * @param {array} actions
     * 
     * @returns {bool}
     */ 
    canSome(actions) {
        return actions.some((action) => this.can(action));
    }
    
    /**
     * Check if current permissions allow perform all of the actions
     * 
     * @param {array} actions
     * 
     * @returns {bool}
     */ 
    canEvery(actions) {
        return actions.every((action) => this.can(action));
    }
}

export default new Permissions();