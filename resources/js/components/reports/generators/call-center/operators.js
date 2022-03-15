import Excel from 'exceljs';
import {dateFormat, timeFormat, phoneNumberFormat} from '@/services/format';
import handbook from '@/services/handbook';
import moment from 'moment';

const durationShortFormat = (sec) => {
    let duration = moment.duration(sec, 'seconds');
    return moment.utc(duration.as('milliseconds')).format('HH:mm:ss');
}

const explainAction = (action) => {
    switch (action) {
        case 'session-start': return __('Начало сессии');
        case 'session-end': return __('Конец сессии');
        case 'pause-start': return __('Пауза');
        case 'pause-end': return __('Конец паузы');
        case 'call-start': return __('Начало звонка');
        case 'call-end': return __('Конец звонка');
        case 'conference-start': return __('Начало конференции');
        case 'conference-end': return __('Конец конференции');
        case 'wrapup-start': return __('Начало обработки');
        case 'wrapup-end': return __('Конец обработки');
        default: return action;
    }
}

const explainStatus = (row) => {
    if (row.process_status) {
        let result = handbook.getOption('call_process_status', row.process_status);
        let source = handbook.getOption('call_log_source', row.process_source);
        if (source) {
            result = `${result} (${source})`;
        }
        return result;
    }
    
    return '';
}

export default (data) => {
    let workbook = new Excel.Workbook();
    let totalPause = 0, totalCall = 0, totalWrapupEnquiry = 0, totalWrapupCall = 0, totalSession = 0;
    let worksheet1 = workbook.addWorksheet(__('Журнал'));
    let worksheet2 = workbook.addWorksheet(__('Сводные данные'));
    let totals = [];
    let map = {};
    let total;
    
    worksheet1.columns = [
        { header: __('Дата'), key: 'data' },
        { header: __('Время'), key: 'time' },
        { header: 'SIP', key: 'sip' },
        { header: __('Оператор'), key: 'operator' },
        { header: __('Действие'), key: 'action' },
        { header: __('Очередь'), key: 'queue' },
        { header: __('Телефон'), key: 'phone' },
        { header: __('Статус'), key: 'status' },
        { header: __('Продолжительность'), key: 'duration' },
    ];
    
    worksheet2.columns = [
        { header: __('Название строк'), key: 'name' },
        { header: __('Пауза'), key: 'pause' },
        { header: __('Простой'), key: 'standby' },
        { header: __('Звонок'), key: 'call' },
        { header: __('Обработка формы с сайта'), key: 'enquiry' },
        { header: __('Общий итог'), key: 'total' },
    ];

    data.forEach((row) => {
        worksheet1.addRow({
            data: dateFormat(row.date),
            time: timeFormat(row.date),
            sip: row.sip,
            operator: row.operator_name,
            action: explainAction(row.action),
            queue: row.process_call_queue,
            phone: phoneNumberFormat(row.phone_number),
            status: explainStatus(row),
            duration: durationShortFormat(row.duration),
        });
        
        if (map[row.operator_id] === undefined) {
            total = map[row.operator_id] = {
                operator: row.operator_name,
                pause: 0,
                call: 0,
                wrapupEnquiry: 0,
                wrapupCall: 0,
                session: 0,
            };
            totals.push(total);
        } else {
            total = map[row.operator_id];
        }
        
        switch (row.action) {
            case 'session-end': 
                total.session += row.duration;
                totalSession += row.duration;
                break;
            case 'pause-end': 
                total.pause += row.duration;
                totalPause += row.duration;
                break;
            case 'call-end': 
                total.call += row.duration;
                totalCall += row.duration;
                break;
            case 'wrapup-end': 
                if (row.process_source === 'site_enquiry') {
                    total.wrapupEnquiry += row.duration;
                    totalWrapupEnquiry += row.duration;
                } else {
                    total.wrapupCall += row.duration;
                    totalWrapupCall += row.duration;
                }
                break;
        }
    });    
    
    totals.forEach((row) => {
        worksheet2.addRow({
            name: row.operator,
            pause: durationShortFormat(row.pause),
            standby: durationShortFormat(Math.max(0, row.session - (row.pause + row.call + row.wrapupEnquiry + row.wrapupCall))),
            call: durationShortFormat(row.call),
            enquiry: durationShortFormat(row.wrapupEnquiry),
            total: durationShortFormat(row.session),
        });
    });

    worksheet2.addRow({
        name: __('Общий итог'),
        pause: durationShortFormat(totalPause),
        standby: durationShortFormat(totalSession - (totalPause + totalCall + totalWrapupEnquiry + totalWrapupCall)),
        call:  durationShortFormat(totalCall),
        enquiry:  durationShortFormat(totalWrapupEnquiry),
        total: durationShortFormat(totalSession),
    });

    return Promise.resolve(workbook);
}