import Excel from 'exceljs';
import {datetimeFormat} from '@/services/format';
import handbook from '@/services/handbook';
import CONSTANT from '@/constants';

const countCallLogs = (row, type) => {
    let result = row.call_type === type ? 1 : 0;
    row.related_actions.forEach((action) => {
        if (action.type === CONSTANT.CALL_ACTION.SUBJECT.CALL_LOG) {
            if (action.call_type === type) {
                result++;
            }
        }
    });
    return result;
}

const countNewAppointments = (row, is_first) => {
    let result = 0;
    row.related_actions.forEach((action) => {
        if (action.type === CONSTANT.CALL_ACTION.SUBJECT.APPOINTMENT) {
            if (action.action === CONSTANT.CALL_ACTION.TYPE.CREATE && action.is_first == is_first) {
                result++;
            }
        }
    });
    return result;
}

const countUpdateAppointments = (row, is_deleted) => {
    let result = 0;
    row.related_actions.forEach((action) => {
        if (action.type === CONSTANT.CALL_ACTION.SUBJECT.APPOINTMENT) {
            if (action.action === CONSTANT.CALL_ACTION.TYPE.UPDATE && action.is_deleted == is_deleted) {
                result++;
            }
        }
    });
    return result;
}

const countCalls = (row, is_non_profile) => {
    let result = 0;
    row.related_actions.forEach((action) => {
        if (action.type === CONSTANT.CALL_ACTION.SUBJECT.CALL) {
            if (action.action === CONSTANT.CALL_ACTION.TYPE.CREATE && action.is_non_profile == is_non_profile) {
                result++;
            }
        }
    });
    return result;
}

const callResult = (row, is_non_profile) => {
    let result = '';
    row.related_actions.forEach((action) => {
        if (action.type === CONSTANT.CALL_ACTION.SUBJECT.CALL) {
            if (action.action === CONSTANT.CALL_ACTION.TYPE.CREATE && action.is_non_profile == is_non_profile) {
                result = action.result;
            }
        }
    });
    return result;
}

export default (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Данные'));
    
    worksheet.columns = [
        { header: __('Начало обработки'), key: 'process_start' },
        { header: __('Завершение обработки'), key: 'process_end' },
        { header: __('Источник'), key: 'source' },
        { header: 'SIP', key: 'sip' },
        { header: __('Оператор'), key: 'operator' },
        { header: __('Очередь'), key: 'queue' },
        { header: __('Кол-во входящих звонков'), key: 'is_incoming_call' },
        { header: __('Кол-во исходящих звонков'), key: 'is_outgoing_call' },
        { header: __('Обработка формы с сайта'), key: 'is_site_enquiry' },
        { header: __('Тип формы с сайта'), key: 'site_enquiry_type' },
        { header: __('Пациент'), key: 'is_patient' },
        { header: __('Не пациент'), key: 'is_not_patient' },
        { header: __('Обработка завершена'), key: 'is_completed' },
        { header: __('Обработка не завершена'), key: 'is_not_completed' },
        { header: __('Обработка невозможна'), key: 'is_unprocessible' },
        { header: __('Причина невозможности обработки'), key: 'unprocessibility_reason' },
        { header: __('Первичное обращение'), key: 'is_first' },
        { header: __('Повторное обращение'), key: 'is_returned' },
        { header: __('Кол-во новых записей первичных пациентов'), key: 'appointment_first' },
        { header: __('Кол-во новых записей вторичных пациентов'), key: 'appointment_returned' },
        { header: __('Кол-во измененных записей'), key: 'appointment_updated' },
        { header: __('Кол-во удаленных записей'), key: 'appointment_deleted' },
        { header: __('Кол-во инф. звонков'), key: 'calls' },
        { header: __('Результат звонка'), key: 'call_result' },
        { header: __('Кол-во инф. звонков (непрофильных)'), key: 'call_nonprofile' },
        { header: __('Результат звонка (непрофильный)'), key: 'call_result_nonprofile' },
        { header: __('Нерезультативный'), key: 'no_result' },
    ];

    data.forEach((row) => {
        worksheet.addRow({
            process_start: datetimeFormat(row.process_start),
            process_end: datetimeFormat(row.process_end),
            source: handbook.getOption('call_log_source', row.source),
            sip: row.sip,
            operator: row.operator,
            queue: row.queue,
            is_incoming_call: countCallLogs(row, CONSTANT.CALL_LOG.TYPES.INCOMING),
            is_outgoing_call: countCallLogs(row, CONSTANT.CALL_LOG.TYPES.OUTGOING),
            is_site_enquiry: row.enquiry_type ? 1 : 0,
            site_enquiry_type: handbook.getOption('enquiry_type', row.enquiry_type) || '',
            is_patient: row.is_patient == 1 ? 1 : 0,
            is_not_patient: row.is_patient == 1 ? 0 : 1,
            is_completed: row.status === CONSTANT.PROCESS_LOG.STATUS.PROCESSED ? 1 : 0,
            is_not_completed: row.status === CONSTANT.PROCESS_LOG.STATUS.NONPROCESSED ? 1 : 0,
            is_unprocessible: row.status === CONSTANT.PROCESS_LOG.STATUS.IMPROCESSIBLE ? 1 : 0,
            unprocessibility_reason: handbook.getOption('reason_impossibility_of_call_processing', row.unprocessibility_reason) || '',
            is_first: row.is_first_visit == 1 ? 1 : 0,
            is_returned: row.is_first_visit == 1 ? 0 : 1,
            appointment_first: countNewAppointments(row, 1),
            appointment_returned: countNewAppointments(row, 0),
            appointment_updated: countUpdateAppointments(row, 0),
            appointment_deleted: countUpdateAppointments(row, 1),
            calls: countCalls(row, 0),
            call_result: callResult(row, 0),
            call_nonprofile: countCalls(row, 1),
            call_result_nonprofile: callResult(row, 1),
            no_result: row.status === CONSTANT.PROCESS_LOG.STATUS.PROCESSED ? 0 : 1,
        });
    });

    return Promise.resolve(workbook);
}