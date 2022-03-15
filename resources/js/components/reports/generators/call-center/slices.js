import Excel from 'exceljs';
import {dateFormat} from '@/services/format';
import handbook from '@/services/handbook';

export default (data) => {
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Данные'));

    worksheet.columns = [
        { header: __('Дата'), key: 'date' },
        { header: __('Специализация'), key: 'specialization' },
        { header: __('Отдел'), key: 'department' },
        { header: __('Звонки'), key: 'calls' },
        { header: __('Записи'), key: 'appointments' },
        { header: __('Приходы'), key: 'income' },
        { header: __('Лечения'), key: 'treatments' },
    ];

    let byDate = {};
    let specializations = {};
    let departmentTypes = {
        'marketing': __('Маркетинг'),
        'reception': __('Регистратура'),
        'other': __('КЦ'),
    };

    data.forEach((row) => {
        let date;
        let spec;
        let department;
        let departmentType = 'other';

        if (row.is_marketing) {
            departmentType = 'marketing';
        } else if (row.is_reception) {
            departmentType = 'reception';
        }

        if (byDate[row.date] === undefined) {
            date = byDate[row.date] = {
                date: row.date,
                calls: 0,
                appointments: 0,
                income: 0,
                treatments: 0,
                bySpec: {},
            };
        } else {
            date = byDate[row.date];
        }

        if (date.bySpec[row.specialization_id] === undefined) {
            spec = date.bySpec[row.specialization_id] = {
                id: row.specialization_id,
                calls: 0,
                appointments: 0,
                income: 0,
                treatments: 0,
                byDepartment: {},
            };
        } else {
            spec = date.bySpec[row.specialization_id];
        }

        if (spec.byDepartment[departmentType] === undefined) {
            department = spec.byDepartment[departmentType] = {
                calls: 0,
                appointments: 0,
                income: 0,
                treatments: 0,
            };
        } else {
            department = spec.byDepartment[departmentType];
        }

        if (specializations[row.specialization_id] === undefined) {
            specializations[row.specialization_id] = row.specialization;
        }

        if (row.is_call == 1) {
            date.calls++;
            spec.calls++;
            department.calls++;
        }
        if (row.is_appointment == 1) {
            date.appointments++;
            spec.appointments++;
            department.appointments++;
        }
        if (row.is_income == 1) {
            date.income++;
            spec.income++;
            department.income++;
        }
        if (row.is_treatment == 1) {
            date.treatments++;
            spec.treatments++;
            department.treatments++;
        }
    });

    Object.keys(byDate).forEach(key => {
        let date = byDate[key];
        let putDate = true;
        Object.keys(date.bySpec).forEach(key => {
            let spec = date.bySpec[key];
            let putSpec = true;
            Object.keys(spec.byDepartment).forEach(key => {
                let department = spec.byDepartment[key];
                worksheet.addRow({
                    date: putDate ? dateFormat(date.date, 'DD.MM.YYYY') : '',
                    specialization: putSpec ? specializations[spec.id] : '',
                    department: departmentTypes[key],
                    calls: department.calls,
                    appointments: department.appointments,
                    income: department.income,
                    treatments: department.treatments,
                });
                putDate = false;
                putSpec = false;
            });
            worksheet.addRow({
                date: '',
                specialization: '',
                department: __('Итого'),
                calls: spec.calls,
                appointments: spec.appointments,
                income: spec.income,
                treatments: spec.treatments,
            });
        });
        worksheet.addRow({
            date: '',
            specialization: '',
            department: __('Итого за день'),
            calls: date.calls,
            appointments: date.appointments,
            income: date.income,
            treatments: date.treatments,
        });
    });

    return Promise.resolve(workbook);
}
