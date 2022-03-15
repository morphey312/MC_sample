import BaseRequest from './base-request';
import {
    required,
    equals,
    ukrSpelling,
    zip,
    missing,
} from '@/services/validation';

class AddressRequest extends BaseRequest
{
    constructor(address, type, strictToUkraine = false) {
        super(address);
        this.addProp('type', () => type);
        this.addProp('country', () => address.country_code, [
            required, 
            ...(strictToUkraine 
                ? [equals('UA').format(__('Вы можете выбрать только страну Украина'))] 
                : [])
        ]);
        this.addProp('area', () => address.region, [required, ukrSpelling]);
        this.addProp('region', () => address.district, [ukrSpelling]);
        this.addProp('settlement', () => address.settlement, [required, ukrSpelling]);
        this.addProp('settlement_type', () => address.settlement_type, [required]);
        this.addProp('settlement_id', () => address.settlement_id, [required]);
        this.addProp('street', () => address.street, [ukrSpelling]);
        this.addProp('street_type', () => address.street_type);
        this.addProp('building', () => address.building, [ukrSpelling]);
        this.addProp('apartment', () => address.apartment, [ukrSpelling]);
        this.addProp('zip', () => address.zip, [zip.or(missing)]);
    }
}

export default AddressRequest;