import Excel from 'exceljs';
import {dateFormat} from '@/services/format';

const dayKey = d => _.padStart(String(d), 3, 'd00');

export default (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Сводная'));
    worksheet.columns = [
        { header: __('Оператор'), key: 'operator' },
        { header: __('Месяц'), key: 'month' },
        { header: __('Звонки'), key: 'calls' },
        { header: __('Записи'), key: 'appointments' },
        { header: __('Приходы'), key: 'income' },
        { header: __('% записей'), key: 'appointments_percent' },
        { header: __('% приходов'), key: 'income_percent' },
        { header: __('Не профильные'), key: 'non_profile' },
    ];
    
    let byOperator = {};
    let byMonth = {};
    
    data.forEach((row) => {
        let operator;
        let month = row.date.substr(0, 7);
        let day = row.date.substr(8, 2);
        let monthData;
        let dayData;
        let totalMonthData;
        
        if (byOperator[row.operator_id] === undefined) {
            operator = byOperator[row.operator_id] = {
                name: row.operator_name,
                calls: 0,
                appointments: 0,
                income: 0,
                nonProfile: 0,
                byMonth: {},
            };
        } else {
            operator = byOperator[row.operator_id]
        }
        
        if (operator.byMonth[month] === undefined) {
            monthData = operator.byMonth[month] = {
                date: row.date,
                calls: 0,
                appointments: 0,
                income: 0,
                nonProfile: 0,
                byDay: {},
            };
        } else {
            monthData = operator.byMonth[month];
        }
        
        if (byMonth[month] === undefined) {
            totalMonthData = byMonth[month] = {
                date: row.date,
                calls: 0,
                appointments: 0,
                income: 0,
                nonProfile: 0,
            };
        } else {
            totalMonthData = byMonth[month];
        }
        
        if (monthData.byDay[day] === undefined) {
            dayData = monthData.byDay[day] = {
                calls: 0,
                appointments: 0,
                income: 0,
                nonProfile: 0,
            };
        } else {
            dayData = monthData.byDay[day];
        }
        
        if (row.is_non_profile == 1) {
            operator.nonProfile++;
            monthData.nonProfile++;
            totalMonthData.nonProfile++;
            dayData.nonProfile++;
        } else if (row.is_call == 1) {
            operator.calls++;
            monthData.calls++;
            totalMonthData.calls++;
            dayData.calls++;
        } else if (row.is_income != 1) {
            operator.calls++;
            monthData.calls++;
            totalMonthData.calls++;
            dayData.calls++;
            operator.appointments++;
            monthData.appointments++;
            totalMonthData.appointments++;
            dayData.appointments++;
        } else {
            operator.income++;
            monthData.income++;
            totalMonthData.income++;
            dayData.income++;
        }
    });
    
    Object.keys(byOperator).forEach(key => {
        let operator = byOperator[key];
        let firstRow = true;
        
        Object.keys(operator.byMonth).forEach((key) => {
            let month = operator.byMonth[key];
            worksheet.addRow({
                operator: firstRow ? operator.name : '',
                month: dateFormat(month.date, 'MMMM'),
                calls: month.calls,
                appointments: month.appointments,
                income: month.income,
                appointments_percent: month.calls > 0 ? Math.round(100 * month.appointments / month.calls) : 0,
                income_percent: month.calls > 0 ? Math.round(100 * month.income / month.calls) : 0,
                non_profile: month.nonProfile,
            });
            firstRow = false;
        });
        
        worksheet.addRow({
            operator: '',
            month: __('Всего'),
            calls: operator.calls,
            appointments: operator.appointments,
            income: operator.income,
            appointments_percent: operator.calls > 0 ? Math.round(100 * operator.appointments / operator.calls) : 0,
            income_percent: operator.calls > 0 ? Math.round(100 * operator.income / operator.calls) : 0,
            non_profile: operator.nonProfile,
        });
    });
    
    let totalCalls = 0, totalAppointments = 0, totalIncome = 0, totalNonProfile = 0, firstRow = true;
    Object.keys(byMonth).forEach((key) => {
        let month = byMonth[key];
        worksheet.addRow({
            operator: firstRow ? __('Суммарно') : '',
            month: dateFormat(month.date, 'MMMM'),
            calls: month.calls,
            appointments: month.appointments,
            income: month.income,
            appointments_percent: month.calls > 0 ? Math.round(100 * month.appointments / month.calls) : 0,
            income_percent: month.calls > 0 ? Math.round(100 * month.income / month.calls) : 0,
            non_profile: month.nonProfile,
        });
        firstRow = false;
        totalCalls += month.calls;
        totalAppointments += month.appointments;
        totalIncome += month.income;
        totalNonProfile += month.nonProfile;
    });
    
    worksheet.addRow({
        operator: '',
        month: __('Всего'),
        calls: totalCalls,
        appointments: totalAppointments,
        income: totalIncome,
        appointments_percent: totalCalls > 0 ? Math.round(100 * totalAppointments / totalCalls) : 0,
        income_percent: totalCalls > 0 ? Math.round(100 * totalIncome / totalCalls) : 0,
        non_profile: totalNonProfile,
    });
    
    Object.keys(byMonth).forEach((month) => {
        let worksheet = workbook.addWorksheet(dateFormat(byMonth[month].date, 'MMMM'));
        worksheet.columns = [
            { header: __('Оператор'), key: 'operator' },
            { header: __('Данные'), key: 'type' },
            ..._.range(1, 32).map(d => {
                return { 
                    header: d, 
                    key: dayKey(d),
                };
            }),
            { header: __('Итого'), key: 'total' },
            { header: '%', key: 'percent' },
        ];
        
        Object.keys(byOperator).forEach(key => {
            let operator = byOperator[key];
            
            if (operator.byMonth[month] !== undefined) {
                let monthData = operator.byMonth[month];
                let days = monthData.byDay;
                let calls = {
                    operator: operator.name, 
                    type: __('Звонки'), 
                    total: monthData.calls,
                    percent: '',
                };
                let appointments = {
                    operator: '', 
                    type: __('Записи'), 
                    total: monthData.appointments,
                    percent: monthData.calls > 0 ? Math.round(100 * monthData.appointments / monthData.calls) : 0,
                };
                let income = {
                    operator: '', 
                    type: __('Приходы'), 
                    total: monthData.income,
                    percent: monthData.calls > 0 ? Math.round(100 * monthData.income / monthData.calls) : 0,
                };
                
                _.range(1, 32).forEach(d => {
                    let k = dayKey(d);
                    calls[k] = appointments[k] = income[k] = 0;
                });
                
                Object.keys(days).forEach(key => {
                    let day = days[key];
                    let k = dayKey(key);
                    calls[k] = day.calls;
                    appointments[k] = day.appointments;
                    income[k] = day.income;
                });
                
                worksheet.addRow(calls);
                worksheet.addRow(appointments);
                worksheet.addRow(income);
            }
        });
    });
    
    return Promise.resolve(workbook);
}