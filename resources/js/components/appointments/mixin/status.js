export default {
    methods: {
        getStatusIdBySystemStatus(statusList = [], systemStatus) {
            let status = statusList.find((status) => { 
                if (status && status.system_status) {
                    return status.system_status == systemStatus;
                }
            });
            return status ? status.id : null;
        },
        getStatusesBySystemStatus(statusList = [], searchList = []) {
            return statusList.filter(status => { 
                return searchList.indexOf(status.system_status) !== -1;
            }).map(status => status.id);
        },
    },
}