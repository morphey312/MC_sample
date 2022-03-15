import Excel from 'exceljs';
import moment from 'moment';
import {dateFormat, timeTotal, listFormat} from '@/services/format';

const minTime = (intervals) => {
    return intervals.length === 0 ? null : _.minBy(intervals, (i) => i.start).start;
}

const maxTime = (intervals) => {
    return intervals.length === 0 ? null : _.maxBy(intervals, (i) => i.end).end;
}

const getSpecializations = (intervals) => {
    let result = [];
    intervals.forEach((i) => {
        result = result.concat(i.specializations);
    });
    return _.uniq(result);
}

const timeFormat = (t) => {
    return moment(t, 'HH:mm:ss').format('HH:mm');
}

export default (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Данные'));

    worksheet.columns = [
        { header: __('День'), key: 'day' },
        { header: __('Дата'), key: 'date' },
        { header: __('Врач'), key: 'doctor' },
        { header: __('Начало рабочего дня'), key: 'shift_start' },
        { header: __('Конец рабочего дня'), key: 'shift_end' },
        { header: __('Специализация'), key: 'specialization' },
        { header: __('Длительность рабочего дня'), key: 'shift_duration' },
        { header: __('Длительность записи'), key: 'appointments_duration' },
        { header: __('Кол-во записавшихся'), key: 'appointments_count' },
        { header: __('Кол-во первичных пациентов'), key: 'appointment_first' },
        { header: __('Кол-во повторных пациентов'), key: 'appointment_returned' },
    ];

    data.forEach((row) => {
        worksheet.addRow({
            day: dateFormat(row.date, 'dd'),
            date: dateFormat(row.date, 'DD.MM.YYYY'),
            doctor: row.doctor.full_name,
            shift_start: timeFormat(minTime(row.time_sheets)),
            shift_end: timeFormat(maxTime(row.time_sheets)),
            specialization: listFormat(getSpecializations(row.time_sheets)),
            appointments_duration: timeTotal(row.doctor.appointments, 'start', 'end'),
            shift_duration: timeTotal(row.time_sheets, 'start', 'end'),
            appointments_count: row.doctor.appointments_first + row.doctor.appointments_returned,
            appointment_first: row.doctor.appointments_first,
            appointment_returned: row.doctor.appointments_returned,
        });
    });

    return Promise.resolve(workbook);
}
