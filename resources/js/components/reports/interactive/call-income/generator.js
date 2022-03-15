import Excel from 'exceljs';

export default (data, type = 'table') => {
    if (type == 'table') {
        return getReportData(data);
    }
    if (type == 'export') {
        return getExportData(data);
    }
}
const getExportData = (data) => {
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

    data.forEach(row => {
        worksheet.addRow({
            operator: row.name || '',
            month: row.date,
            calls: row.calls,
            appointments: row.appointments,
            income: row.income,
            appointments_percent: row.calls > 0 ? Math.round(100 * row.appointments / row.calls) : 0,
            income_percent: row.calls > 0 ? Math.round(100 * row.income / row.calls) : 0,
            non_profile: row.nonProfile,
        });
    });

    return workbook;
}

const getReportData = (data) => {
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
        } else if ((row.is_call == 1 && row.is_first == 1 && row.is_appointment == 1) || (row.is_call == 1 && row.is_appointment != 1)) {
            operator.calls++;
            monthData.calls++;
            totalMonthData.calls++;
            dayData.calls++;
        }

        if (row.is_first) {
            if (row.is_appointment == 1) {
                operator.appointments++;
                monthData.appointments++;
                totalMonthData.appointments++;
                dayData.appointments++;
            }

            if (row.is_income == 1) {
                operator.income++;
                monthData.income++;
                totalMonthData.income++;
                dayData.income++;
            }
        }
    });

    return {byOperator, byMonth};
}
