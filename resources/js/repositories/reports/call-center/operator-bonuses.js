import BaseReportRepository from '../base-report-repository';
import PositionRepository from '@/repositories/employee/position';

const qs = require('qs');

class OperatorBonusesReportRepository extends BaseReportRepository
{
    constructor() {
        super('/api/v1/reports/call-center/operator-bonuses');
        this.clinicEndpoint = '/api/v1/bonus-norms';
    }

    fetch(filters = null) {
        delete filters.clinic;

        return this.fetchData(filters).then(totals => {
            return this.fetchClinicNorms().then(clinics => {
                return this.fetchOperatorNorms(filters).then(operators => {
                    return this.fetchPositions().then(positions => {
                        return Promise.resolve({
                            totals,
                            clinics: clinics.data,
                            operators,
                            positions,
                        });
                    });
                });
            });
        });
    }

    fetchData(filters, url = null) {
        return this.fetchInternal(this.buildUrl(url, {
            ...this.getFilters(filters),
        }), false);
    }

    fetchClinicNorms() {
        let queryString = qs.stringify({scopes: ['clinic'], limit: 50});
        return this.fetchListInternal(`${this.clinicEndpoint}?${queryString}`, false);
    }

    fetchOperatorNorms(filters) {
        return this.fetchInternal(this.buildUrl('operator-norms', {
            ...this.getFilters({
                position_type: 'operator',
            }),
            date_start: filters.date_start,
            date_end: filters.date_end,
            scopes: ['operator_bonus', 'clinics'],
            sort: [{field: 'full_name', direction: 'asc'}],
        }), false);
    }

    fetchPositions() {
        let position = new PositionRepository();
        let filters = {
            or: [
                {is_operator: true},
                {is_superviser: true},
            ]
        };
        return position.fetchList(filters);
    }
}

export default OperatorBonusesReportRepository;
