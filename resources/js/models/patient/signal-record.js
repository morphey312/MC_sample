import BaseModel from '@/models/base-model';
import {
    required,
    dependsOn,
    maxlen,
    STRING_MAX_LEN,
    TEXT_MAX_LEN
} from '@/services/validation';

class SignalRecord extends BaseModel
{
    /**
     * @inheritdoc
     */
    defaults() {
        return {
            id: null,
            patient_id: null,
            blood_group: null,
            rhesus_factor: null,
            diabetes: null,
            transfusion: null,
            transfusion_comment: null,
            drug_intolerance: null,
            infections: null,
            surgical_interventions: null,
            allergic_history: null,
            patient_feedback: null,
            onco_observation_gyn: null,
            onco_observation_gyn_date: null,
            onco_observation_pro: null,
            onco_observation_pro_date: null,
            onco_observation_uro: null,
            onco_observation_uro_date: null,
            onco_observation_ren: null,
            onco_observation_ren_date: null,
            onco_observation_vil: null,
            onco_observation_vil_date: null,
            onco_observation_vaserman: null,
            onco_observation_vaserman_date: null
        };
    }

    /**
     * @inheritdoc
     */
    validation() {
        return {
            blood_group: required,
            rhesus_factor: required,
            diabetes: required,
            transfusion_comment: maxlen(STRING_MAX_LEN),
            drug_intolerance: maxlen(TEXT_MAX_LEN),
            infections: maxlen(TEXT_MAX_LEN),
            surgical_interventions: maxlen(TEXT_MAX_LEN),
            allergic_history: maxlen(TEXT_MAX_LEN),
            patient_feedback: maxlen(TEXT_MAX_LEN),

            onco_observation_gyn: dependsOn("onco_observation_gyn_date"),
            onco_observation_pro: dependsOn("onco_observation_pro_date"),
            onco_observation_uro: dependsOn("onco_observation_uro_date"),
            onco_observation_ren: dependsOn("onco_observation_ren_date"),
            onco_observation_gyn_date: dependsOn("onco_observation_gyn"),
            onco_observation_pro_date: dependsOn("onco_observation_pro"),
            onco_observation_uro_date: dependsOn("onco_observation_uro"),
            onco_observation_ren_date: dependsOn("onco_observation_ren"),
            onco_observation_vil: dependsOn("onco_observation_vil_date"),
            onco_observation_vil_date: dependsOn("onco_observation_vil"),
            onco_observation_vaserman: dependsOn("onco_observation_vaserman_date"),
            onco_observation_vaserman_date: dependsOn("onco_observation_vaserman")
        };
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/patients/signal-records',
            fetch: '/api/v1/patients/signal-records/{id}',
            update: '/api/v1/patients/signal-records/{id}',
        }
    }
}

export default SignalRecord;
