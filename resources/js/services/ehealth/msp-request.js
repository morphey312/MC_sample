import BaseRequest from './base-request';
import AddressRequest from './address-request';
import OwnerRequest from './owner-request';
import AccreditationRequest from './accreditation-request';
import LicenseRequest from './license-request';
import ArchiveRequest from './archive-request';
import {
    required,
    email,
    edrpou,
    ukrSpelling,
} from '@/services/validation';

const MSP_TYPES = {
    'emergency': 'EMERGENCY',
    'outpatient': 'OUTPATIENT',
    'pharmacy': 'PHARMACY',
    'primary_care': 'PRIMARY_CARE',
};

class MspRequest extends BaseRequest
{
    constructor(msp, {redirectUrl, consentText}) {
        super(msp);
        this.addProp('edrpou', () => msp.edrpou, [required, edrpou]);
        this.addProp('type', () => this.fromDict(msp.type, MSP_TYPES), [required]);
        this.addProp('residence_address', new AddressRequest(msp.address, 'RESIDENCE', true), false, 'address');
        this.addProp('phones', () => this.makePhones({
            phone: msp.phone, 
            additional_phone: msp.additional_phone,
        }));
        this.addProp('email', () => msp.email, [required, email]);
        this.addProp('website', () => msp.website);
        this.addProp('receiver_funds_code', () => msp.receiver_funds_code, [ukrSpelling]);
        this.addProp('beneficiary', () => msp.beneficiary, [ukrSpelling]);
        this.addProp('owner', new OwnerRequest(msp.owner, msp.owner_position));
        this.addProp('accreditation', () => this.makeAccreditation(msp));
        this.addProp('license', () => this.makeLicense(msp));
        this.addProp('archive', () => this.makeArchives(msp.archives));
        this.addProp('security', () => this.makeSecurity(redirectUrl));
        this.addProp('public_offer', () => this.makePublicOffer(consentText));
    }

    makeAccreditation(msp) {
        let request = new AccreditationRequest(msp);
        return request.transform().then(() => {
            request.getErrors().forEach((err) => {
                this.addError(err.prop, err.error);
            });
            return request.getData();
        });
    }

    makeLicense(msp) {
        let request = new LicenseRequest(msp);
        return request.transform().then(() => {
            request.getErrors().forEach((err) => {
                this.addError(err.prop, err.error);
            });
            return request.getData();
        });
    }

    makeArchives(archives) {
        return Promise.all(archives.map((doc, index) => {
            let request = new ArchiveRequest(doc);
            return request.transform().then(() => {
                request.getErrors().forEach((err) => {
                    this.addError(`archives.${index}.${err.prop}`, err.error);
                });
                return request.getData();
            });
        }));
    }

    makeSecurity(redirectUrl) {
        return {
            redirect_uri: redirectUrl,
        };
    }

    makePublicOffer(consentText) {
        return {
            consent_text: consentText,
            consent: true,
        };
    }
}

export default MspRequest;