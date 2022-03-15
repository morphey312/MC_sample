export default {
    ENCTOUNTER: {
        TYPE_REFERRAL: {
            NONE: 'none',
            PAPER: 'paper',
            INCOMING: 'incoming',
        }
    },
    ENCOUNTERS: {
        REFERRAL_TYPE: {
            NONE: 'none',
            PAPER: 'paper',
            INCOMING: 'incoming'
        }
    },
    COLORS: {
        ANALYSIS: {
            PASSED: '#FF1900',
            EMAIL_SENT: '#88FF00',
            READY: '#FFEA00',
        },
        ANALYSIS_CONTAINERS_WARNING: {
            NOT_PAYED: '#f57161',
            BY_POLICY: '#46a500',
            LEGAL_ENTITY: '#0038a5',
        },

    },
    DISCOUNT_TYPE: {
        RELOAD: 2,
    },
    CLIENT_TYPES: {
        NEW: 'new',
        EXISTING: 'existing'
    },
    CALL: {
        REQUEST_TYPES: {
            REPEATED: 0,
            FIRST: 1,
        },
    },
    CALL_LOG: {
        TYPES: {
            INCOMING: 'incoming',
            OUTGOING: 'outgoing',
        },
        SOURCE: {
            CALL: 'call',
            CALLBACK: 'callback',
            WEBCALLBACK: 'webcallback',
        },
    },
    PROCESS_LOG: {
        STATUS: {
            PROCESSED: 'processed',
            NONPROCESSED: 'nonprocessed',
            IMPROCESSIBLE: 'improcessible',
        },
        PATIENT_TYPE: {
            PATIENT: '1',
            NOT_PATIENT: '0',
        },
        VISIT_TYPE: {
            FIRST: '1',
            RETURN: '0',
        },
        IMPROCESSIBLE_REASON_OTHER: '0',
    },
    CALL_ACTION: {
        TYPE: {
            CREATE: 'create',
            UPDATE: 'update',
        },
        SUBJECT: {
            PATIENT: 'patient',
            CALL: 'call',
            APPOINTMENT: 'appointment',
            CALL_LOG: 'call_log',
        },
    },
    EMPLOYEE: {
        STATUSES: {
            WORKING: 'working',
            NOT_WORKING: 'not_working',
            REMOVED: 'removed',
            PROBATION: 'probation',
        },
        POSITIONS: {
            OPERATOR: 'operator',
            DOCTOR: 'doctor',
            CASHIER: 'cashier',
            SURGERY: 'surgery',
        },
        SYSTEM_STATUSES: {
            ONLINE_PAYMENT: 'for_online_payment',
        },
        PLAN_SERVICE_MARKS: {
            HAL_RAR: 'hal_rar',
        },
        SURGERY_ROLES: {
            SURGEONIST: 'surgeonist',
            ANESTHESIOLOGIST: 'anesthesiologist',
            ASSISTANT: 'assistant',
            NURSE: 'nurse',
        },
    },
    PATIENT: {
        STATUS: {
            PATIENT: 'patient',
            GUEST: 'guest',
        },
        MED_INSURANCE: {
            HAS_INSURANCE: 'yes',
            NOT_KNOWN: 'n_a',
            NO_INSURANCE: 'no',
        },
        REGISTRATION: {
            STATUS: {
                NEW: 'new',
                REGISTERED: 'registered',
                CONFIRMED: 'confirmed',
            },
        },
    },
    USER: {
        TYPE: {
            EMPLOYEE: 'employees',
            PATIENT: 'patient',
        },
    },
    CURRENCY: {
        UAH: 'uah',
    },
    COUNTRY_CODE: {
        UA: 'UA',
    },
    PRICE: {
        SET_TYPE: {
            BASE: 'base',
            CERTIFICATE: 'certificate',
            INSURANCE: 'insurance',
        },
        SERVICE_TYPE: {
            SERVICE: 'service',
            ANALYSIS: 'analysis',
        },
        EXISTENS: {
            NO_MATTER_PRICE: 1,
            HAS_PRICE: 2,
            HAS_PRICE_WITH_PERIOD: 3,
            HAS_NO_PRICE: 4,
            HAS_NO_PRICE_WITH_PERIOD: 5,
        }
    },
    CALL_REQUEST: {
        STATUS: {
            MADE: 'made',
            CANCELLED: 'canceled',
            SUCCESS_CALL: 'success_call',
        },
    },
    SCHEDULE_TIME_STEP: 5,
    APPOINTMENT: {
        TYPES: {
            REPEATED: 0,
            FIRST: 1,
        },
        STATUSES: {
            SIGNED_UP: 'signed_up',
            CAME_TO_RECEPTION: 'came_to_reception',
            DID_NOT_COME: 'didnt_come',
            DELETED: 'deleted',
            CAME_TO_DOCTOR: 'came_to_doctor',
            COMPLETED: 'completed',
            ANALYSES_READY: 'analyses_ready',
            PAYED: 'payed',
        },
        TIME_MAX: {
            ORDINARY: 420,
            EXTENDED: 1435,
        }
    },
    INSURANCE_ACT : {
        STATUSES: {
            PAYED: 'payed',
            PARTLY: 'partly_payed',
            NEW: 'new',
            CREATED: 'created',
        },
    },
    PERSONAL_TASK: {
        STATUS: {
            NEW: 'new',
            STARTED: 'started',
            DEFERRED: 'deferred',
            COMPLETED: 'completed',
        },
    },
    TIME_PICKER_OPTIONS: {
        START: '00:00',
        END: '23:55',
        STEP: '00:05',
    },
    REPOSITORY_CACHE: false,
    DAY_SHEET: {
        OWNER_TYPES: {
            EMPLOYEE: 'employees',
            WORKSPACE: 'workspaces',
        },
        GRID_PERIOD: {
            START: '23:55:00',
            END: '00:00:00',
        },
    },
    MEDIA_TYPE: {
        OTHER: 'other',
    },
    CARD_RECORD: {
        TYPE: {
            OUTPATIENT_RECORD: 'outpatient_record',
            DIARY_RECORD: 'diary_record',
            SERVICE_RECORD: 'service_record',
            CONDITION_RECORD: 'condition_record',
            CARD_ASSIGNMENT: 'card_assignment',
            PROTOCOL_RECORD: 'protocol_record',
            OUTCLINIC_PROTOCOL_RECORD: 'outclinic_protocol_record',
            TREATMENT_ASSIGNMENT: 'treatment_assignment',
            CONSULTATION_RECORD: 'consultation_record',
            PATIENT_DOCUMENT: 'patient_card_document',
            NEXT_VISIT: 'next_visit',
            PRINTED_DOCUMENT: 'patient_card_printed_document',
            MANIPULATION_RECORD: 'manipulation_record'
        },
        PRINTED_DOCUMENT_TYPES: {
            PrintAdvisory: 'Консультативное заключение'
        },
        SECTIONS: {
            OPERATION_PROTOCOL: 'operation-protocol',
            EPICRISIS: 'epicrisis',
            ANESTHESIOLOGY: 'anesthesiology',
            ANESTHESIOLOGY_PROTOCOL: 'anesthesiology-protocol',
            EPIDEMIOLOGICAL_MAP: 'epidemiological-map',
            HOSPITALIZATION_DATA: 'hospitalization-data',
            LABOR_RECOMENDATIONS: 'labor-recomendations',
        },
    },
    PAYMENT_DESTINATION: {
        ADDITIONAL_MARK: {
            FOR_ANALYSES: 'for_analyses',
            FOR_DIAGNOSTICS: 'for_diagnostics',
            FOR_MEDICINES: 'for_medicines',
            FOR_ANESTHESIA: 'for_anesthesia',
            FOR_OPERATION: 'for_operation',
        }
    },
    ANALYSIS_RESULT: {
        STATUSES: {
            ASSIGNED: 'assigned',
            PASSED: 'passed',
            TEST_IN_OTHER_LABORATORY: 'test_in_other_laboratory',
            ASSIGNED_BUT_NOT_BE_TEST: 'assigned_but_not_be_test',
            EMAIL_SENT: 'email_sent',
        },
        PAYMENT_STATUSES: {
            PAYED: 'payed',
            PARTLY: 'partly_payed',
            IN_DEBT: 'in_debt',
        },
    },
    CARD_ASSIGNMENT: {
        TYPES: {
            ANALYSIS_RESULTS: 'analysis_results',
            ASSIGNED_MEDICINES: 'assigned_medicines',
            ASSIGNED_PROCEDURES: 'assigned_procedures',
            ASSIGNED_PHYSIOTHERAPIES: 'assigned_physiotherapies',
            ASSIGNED_DIAGNOSTICS: 'assigned_diagnostics',
            OUTCLINIC_SERVICES: 'outclinic_services',
            SURGERY_BASE_SERVICES: 'surgery_base_services',
            SURGERY_SERVICES: 'surgery_services',
        },
    },
    SPECIALIZATION: {
        SERVICE_GROUPS: {
            PROCEDURE: 'procedure',
            PHYSIOTHERAPY: 'physiotherapy',
            SURGERY: 'surgery',
            ANALYSIS: 'analysis',
            ANESTHESIA: 'anesthesia',
            ULTRASOUND: 'ultrasound',
            EMERGENCY: 'emergency',
        },
    },
    PAYMENT: {
        TYPES: {
            INCOME: 'income',
            EXPENSE: 'expense',
        },
        KINDS: {
            HAS_APPOINTMENT: 'has_appointment',
            DEPOSIT: 'deposit',
            PREPAYMENT: 'prepayment',
        }
    },
    ASSIGNED_MEDICINE: {
        TYPES: {
            MEDICINE: 'medicine',
            OUTCLINIC_MEDICINE: 'outclinic_medicine',
        },
    },
    APPOINTMENT_SERVICE: {
        SERVICE_TYPE: {
            SERVICE: 'service',
            ANALYSIS: 'analysis',
        },
        CONTAINERS: {
            ANALYSES: 'analysis_results',
            MEDICINES: 'medicines',
        },
        ITEM_TYPES: {
            ASSIGNED_MEDICINE: 'assigned_medicine',
            ANALYSIS_RESULT: 'analysis_result',
        }
    },
    SITE_ENQUIRY: {
        STATUS: {
            NEW: 'new',
            PROCESSED: 'processed',
            NOT_PROCESSED: 'not_processed',
        },
        TYPE: {
            APPOINTMENT: 'appointment',
            PRICE: 'price',
            QUESTION: 'question',
            COVID_TEST: 'covid-test',
            TOMOGRAPHY: 'tomography',
        },
        SERVICE_TYPE: {
            SERVICE: 'service',
            ANALYSIS: 'analysis',
        },
        PAYMENT_STATUS: {
            PAID: 'paid',
        },
        SERVICE_REFUND: {
            STATUS_REFUND: 'to_refund',
            STATUS_REFUNDED: 'refunded',
        },
    },
    CLINIC: {
        BLANK_TYPE: {
            HEADER: 'header',
            FOOTER: 'footer',
            QUESTIONNAIRE_BLANK: 'questionnaire_blank'
        },
        KIND: {
            AMBULANT_CLINIC: 'ambulant_clinic',
            CLINIC: 'clinic',
            DRUGSTORE: 'drugstore',
            DRUGSTORE_POINT: 'drugstore_point',
            FAP: 'fap',
            LICENSED_UNIT: 'licensed_unit',
        },
        STATUS: {
            ACTIVE: 1,
            INACTIVE: 0,
        },
    },
    EHEALTH_PATIENT: {
        DOCUMENTS_TYPE: {
            NATIONAL_ID: 'national_id',
            RELATIONSHIP: 'RELATIONSHIP'
        },
        DOCUMENTS_OWNER: {
            PERSON: 'person',
            CONFIDANT_PERSON: 'confidant_person'
        },
        STATUS: {
            NEW: 'NEW',
            APPROVED: 'APPROVED',
            SIGNED: 'SIGNED',
            CANCELED: 'CANCELED',
        },
        AUTHENTICATION_ERRORS: {
            INVALID_CODE: 'Invalid verification code',
            MAXIMUM_ATTEMPTS: 'Maximum attempts exceed'
        },
        AUTHENTICATION_METHODS: {
            INSERT: 'INSERT',
            DEACTIVATE: 'DEACTIVATE',
            UPDATE: 'UPDATE',
        },
        AUTHENTICATION_TYPE: {
            OTP: 'otp',
            OFFLINE: 'offline',
            THIRD_PERSON: 'third_person',
        }
    },
    MSP: {
        ACCREDITATION_CATEGORY: {
            NONE: 'no_accreditation',
        },
    },
    NOTIFICATION: {
        CHANNEL: {
            SMS: 'sms',
            EMAIL: 'email',
            TELEGRAM: 'telegram',
        },
        DELIVERY_STATUS: {
            NO_DELIVERY: 'none',
            RE_DELIVERY: 're_send',
            DELIVERING: 'pending',
            DELIVERY_OK: 'ok',
            DELIVERY_FAILED: 'failed',
            DELIVERY_OK_FAILED: 'ok_failed',
        },
        PROVIDER: {
            ESPUTNIK: 'esputnik',
        },
        PROVIDER_STATUS: {
            DEBUG: 'debug',
            SEND: 'send',
        },
    },
    PRINT_LOG: {
        ADVISORY: 'PrintAdvisory',
        FILE: 'PrintFile'
    },
    SCHEDULE_CELL_HEIGHT: 24,
    WAIT_LIST_RECORD: {
        STATUS: {
            NEW: 'new',
            PROCESSED: 'processed',
            NOT_PROCESSED: 'not_processed',
            CANCELED: 'canceled',
            PAUSE: 'pause',
        },
    },
    ELASTICSEARCH: {
        INDICES: {
            CITIES_SUGGESTER: 'cities_suggester',
            INCOME_APPOINTMENTS: 'income_appointments',
            INCOME_PAYMENTS: 'income_payments',
            REDIRECTS_EXTERNAL: 'redirects_external',
            REDIRECTS_INTERNAL: 'redirects_internal',
            REDIRECTS_EXTERNAL_V3: 'redirects_external_v3',
            REDIRECTS_INTERNAL_V3: 'redirects_internal_v3',
            CALL_CENTER_SLICES: 'call_center_slices',
            CALL_CENTER_BONUSES: 'call_center_bonuses',
            CALL_CENTER_SESSION_LOGS: 'call_center_session_logs',
            CALL_CENTER_LOGS: 'call_center_logs',
            ENCOUNTERS: 'encounters',
        },
        HITS_SIZE: 1500,
        CLUSTERS: {
            DEFAULT: 'default',
            CITIES: 'cities',
        },
    },
    NOTIFICATION_TEMPLATE: {
        SCENARIO: {
            PREPARATION: 'preparation',
            MANUAL: 'sms_appointment_reminder_operator_manual',
            SMS_MISSED_CALL: 'sms_missed_call',
        }
    },
    NOTIFICATION_MAILING_TEMPLATE: {
        SCENARIO: {
            MISSED_FIRST_APPOINTMENTS: 'send-missed-first-appointments',
        }
    },
    SMS_REMINDERS: {
        TYPE: {
            AUTO: 'auto',
            MANUAL: 'manual'
        },
        STATUS: {
            NONE: 'none'
        }
    },
    EHEALTH: {
        STATUS: {
            NEW: 'new',
            CANCELLED: 'cancelled',
            SUCCESS: 'success',
            FAILED: 'failed',
            WAIT_AUTH: 'wait_auth',
        },
        ACTION: {
            CREATE: 'create',
            UPDATE: 'update',
            APPROVE: 'approve',
            SIGN: 'sign',
            ENABLE: 'enable',
            DISABLE: 'disable',
        },
        EMPLOYEE_TYPE: {
            ADMIN: 'ADMIN',
            DOCTOR: 'DOCTOR',
            SPECIALIST: 'SPECIALIST',
            PHARMACIST: 'PHARMACIST',
            OWNER: 'OWNER',
            HR: 'HR',
            ASSISTANT: 'ASSISTANT',
        },
        CONTRACT_TYPE: {
            CAPITATION: 'capitation',
            REIMBURSEMENT: 'reimbursement',
        },
        CONTRACT_STATUS: {
            NEW: 'NEW',
            IN_PROCESS: 'IN_PROCESS',
            DECLINED: 'DECLINED',
            APPROVED: 'APPROVED',
            PENDING_NHS_SIGN: 'PENDING_NHS_SIGN',
            NHS_SIGNED: 'NHS_SIGNED',
            SIGNED: 'SIGNED',
            TERMINATED: 'TERMINATED',
            VERIFIED: 'VERIFIED',
        },
        MSP_TYPE: {
            EMERGENCY: 'emergency',
            OUTPATIENT: 'outpatient',
            PHARMACY: 'pharmacy',
            PRIMARY_CARE: 'primary_care',
        },
    },
    PLACE_OCCUPATION: {
        STATUS: {
            RESERVED: 'reserved',
            OCCUPIED: 'occupied',
        },
        TIMELINE: {
            LEVEL: {
                DEPARTMENT: 1,
                ROOM: 2,
                PLACE: 3,
            },
            ITEM_TYPE: 'range',
        },
    },
    STATIONAR_MOZ_BLANK: {
        STATIONAR_CARD: 'stationar_card',
        INSPECTION_AND_ANESTHESIA: 'inspection_and_anesthesia',
        DISCHARGED_CARD: 'discharged_card',
        STATIONAR_CARD_EXTRACTION: 'stationar_card_extraction',
        DOCTOR_ASSIGNMENT_LIST: 'doctor_assignment_list',
        DISCHARGE_DIARY: 'discharge_diary',
        EPIDEMIOLOGICAL_MAP: 'epidemiological_map',
    },
    SENTRY: {
        DSN: 'https://c01d80252d14452f98625f9c3a534b3c@sentry.medcenterplus.com/2',
    },
    TRANFER_SHEET_STATUES: {
        NEW: 'new',
        TRANSPORTING: 'transporting',
    },
    PRICE_AGREEMENT_ACT: {
        STATUSES: {
            AGREED: 'agreed',
            NOT_AGREED: 'not_agreed',
            IN_WORK: 'in_work'
        },
        TYPE: {
            SERVICES: 'service',
            ANALYSIS: 'analysis',
        },
        PRICE: {
            CHANGE_TYPE: {
                CHANGE_COST: 'change_cost',
                CLOSE_PRICE: 'close_price',
                ADD_CLINIC: 'add_clinic',
                NEW_PRICE: 'new_price',
            }
        },
    },
    EVENT_ACTIONS: {
        SHOW_MODAL_AGREEMENT_ACT: 'show_price_agrement_prices'
    },
    CHECKBOX_CHECKS: {
        TYPE: {
            EXTRACT: 'extract',
            TOKEN: 'token',
            X_REPORT: 'xreport',
            Z_REPORT: 'zreport',
        },
    },
}
