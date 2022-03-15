import Excel from 'exceljs';
import moment from 'moment';
import {numberFormat} from '@/services/format';
import CONSTANS from '@/constants';

const UZI = __('УЗИ');
const EKG = __('ЭКГ');
const ENK = __('ЭНК');
const END = __('ЭНД');
const EEG = __('ЭЭГ');
const SINGLE_TIME_SPEC = [UZI, EKG, ENK, END, EEG];
const BONUS_PERIOD = 30;
const BONUS_PERCENT = 10;
const BORDER_STYLE = {style:'medium', color: {argb:'000'}};
const SKIP_SERVICE_IDS = [155, 1244, 1269, 1927, 2093, 2318, 2711, 2908, 2975, 3070, 3165, 3166, 3314, 3315, 3404];
const SKIP_SERVICE_UZI = [330, 331, 332, 333, 335, 596, 650, 654, 676, 677, 886, 887, 1070, 1071, 1277, 1278, 1279, 1280, 
    1281, 2034, 2020, 2688, 2689, 2690, 3171, 3172, 3420, 3508, 3509, 3795, 3796, 3876, 4130];
const SKIP_UZI_SPECIALIZATIONS = [2, 5];
const INCREASED_PERCENT_SPECIALIZATION = 4;
const MILLISECONDS_PER_DAY = 86400000;
    
const getExternalPeriodPatients = (data, sources) => {
    for (let source in sources) {
        sources[source].external = groupList(findExternalSourceAppointments(data, source));
    }
    return sources;
}

const getInternalPeriodPatients = (data, sources) => {
    for (let source in sources) {
        sources[source].internal = groupList(findInternalSourceAppointments(data, source, sources[source]));
    }
    return sources;
}

const findInternalSourceAppointments = (data, sourceId, source) => {
    return data.filter(row => {
        return (row.specialization_visit_source == sourceId && _.isVoid(row.appointment_source_id)) || row.appointment_source_id == sourceId;
    });
}

const findExternalSourceAppointments = (data, source) => {
    return data.filter(row => {
        return source == row.redirect_source.source_id && 
                (_.isVoid(row.patient_visit_date) || getDaysDiff(row.date, row.patient_visit_date) <= BONUS_PERIOD);
    });
}

const groupList = (list, groupField = 'specializationCard') => {
    return _.groupBy(list, groupField);
}

const getDaysDiff = (dateFrom, dateTo) => {
    let timeDiff = Math.abs(new Date(dateFrom).getTime() - new Date(dateTo).getTime());
    return Math.ceil(timeDiff / MILLISECONDS_PER_DAY);
}

const getSources = (data) => {
    let sources = {};

    data.forEach(row => {
        if (row.redirect_source == null) {
            return;
        }

        let patientSource = row.redirect_source.source_id
        if (_.isFilled(row.redirect_source.employee.employee_id) && sources[patientSource] == undefined) {
            sources[patientSource] = {
                name: row.redirect_source.source_name,
                employee: row.redirect_source.employee.full_name,
                employee_id: row.redirect_source.employee.employee_id,
                position: getPosition(row.redirect_source.employee.positions),
            };
        }
    });
    return sources;
}

const addFullCards = (data) => {
    return data.map(row => {
        row.specializationCard = (row.patient_card ? row.patient_card.number : '') + getRowCardSpecialization(row);
        return row;
    });
}

const getRowCardSpecialization = (appointment) => {
    let card;
    if (appointment.patient_card) {
        card = appointment.patient_card.specializations.find(spec => spec.specialization_id == appointment.card_specialization_id);
    }
    return (card && card.specialization) ? card.specialization.short_name : '';
}

const getPosition = (positions) => {
    let keys = Object.keys(positions);
    if (keys.length !== 0) {
        return positions[keys[0]].name;
    }
    return '';
}

const getColumns = () => {
    let style = {
        alignment: {vertical: 'middle'},
    }
    let width = 25;
    let columns = [
        {header: __('ФИО сотрудника'), key: 'employee', width, style},
        {header: __('Отделение'), key: 'position', width, style},
        {header: __('Карта пациента'), key: 'externalCard', width, style},
        {header: __('Сумма дохода за внешнее перенаправление'), key: 'externalPayed', width, style},
        {header: __('Оплата сотруднику'), key: 'externalPercent', width, style},
        {header: __('Карта пациента'), key: 'internalCard', width, style},
        {header: __('Сумма дохода за перенаправление'), key: 'internalPayed', width, style},
        {header: __('Оплата сотруднику'), key: 'internalPercent', width, style},
        {header: __('ДОХОД ИТОГО'), key: 'totalPayed', width, style},
        {header: __('ОПЛАТА СОТРУДНИКУ ИТОГО'), key: 'totalPercent', width, style},
    ];

    return columns;
}

const getCardPayed = (appointments) => {
    return appointments.reduce((payed, row) => {
        return payed + Number(row.services.reduce((total, service) => {
            return total + Number(service.payed);
        }, 0));
    }, 0);
}

const getPayedPercent = (payed) => {
    return (payed == 0) ? 0 : (payed / 100) * BONUS_PERCENT;
}

const getInternalAppointments = (data, filters) => {
    let list = [];
    let dateStart = filters.date_start;
    data.forEach(row => {
        if (_.isFilled(row.appointment_source_id)) {
            if (row.is_first == 1) {
                let appointments = filterInternalAppointments(data, row, dateStart);
                list = [...list, ...appointments];

                if (row.date < dateStart) {
                    let appointment = filterActualPayments(row, dateStart, filters.date_end);
                    if (appointment) {
                        list = addInternalAppointment(list, appointment);
                    }
                }
            }

            if (row.date >= dateStart) {
                list = addInternalAppointment(list, row);
            }
        } else {
            if (row.date >= dateStart) {
                list = addInternalAppointment(list, row);
            }
        }
    });
    return list;
}

const addInternalAppointment = (list, row) => {
    let index = list.findIndex(item => item.id == row.id);
    if (index === -1) {
        list.push(row);
    }
    return list;
}

const filterInternalAppointments = (data, row, filterDate) => {
    return data.filter(item => {
        return (getDaysDiff(item.date, row.date) <= BONUS_PERIOD) &&
               (item.date >= filterDate) && 
               (item.id != row.id) && 
               (item.is_first == 0) && 
               (row.card_specialization_id === item.card_specialization_id) &&
               (row.patient_id === item.patient_id) &&
               _.isVoid(row.appointment_source_id)
    });
}

const filterActualPayments = (data, dateStart, dateEnd) => {
    let services = data.services.filter(service => {
        if (SKIP_SERVICE_IDS.indexOf(service.service_id) !== -1 || service.payed == 0) {
            return false;
        }

        let payments = service.payments.filter(payment => {
            return payment.date >= dateStart && payment.date <= dateEnd;
        });

        if (payments.length != 0) {
            service.payments = payments;
            return true;
        }
        return false;
    });

    if (!services || services.length == 0) {
        return false;
    }
    services.map(service => {
        service.payed = service.payments.reduce((sum, payment) => {
            if (payment.type === CONSTANS.PAYMENT.TYPES.INCOME) {
                sum += Number(payment.payed_amount);
            } else if (payment.type === CONSTANS.PAYMENT.TYPES.EXPENSE) {
                sum -= Number(payment.payed_amount);
            }
            return sum;
        }, 0);
        return service;
    })
    data.services = services;
    return data;
}

const filterPayments = (list, filters) => {
    let dateStart = filters.date_start;
    let dateEnd = filters.date_end;
    
    return list.filter(row => {
        let appointment = filterActualPayments(row, dateStart, dateEnd);
        if (appointment) {
            row = appointment;
            return true;
        }
        return false;
    });
}

export default (data, filters) => {
    data.internal = filterPayments(data.internal, filters);
    data.external = filterPayments(data.external, filters);
    let internalList = addFullCards(getInternalAppointments(data.internal, filters));
    let sources = getSources([...data.external, ...data.internal]);
    let externalList = addFullCards(data.external);
    let groups = getExternalPeriodPatients(externalList, sources);
    groups = getInternalPeriodPatients(internalList, sources);

    const workbook = new Excel.Workbook();
    const worksheet = workbook.addWorksheet(__('Данные'), {
        views: [
            {state: 'frozen', ySplit: 1, xSplit: 1},
        ],
    });

    worksheet.columns = getColumns();
    
    let titleRow = worksheet.getRow(2);
    titleRow.height = 30;
    titleRow._cells.forEach(item => {
        let cell = worksheet.getCell(item._address);
        cell.alignment  = {
            wrapText: true,
            horizontal: 'center',
            vertical: 'middle',
        };
        cell.font = {
            bold: true,
            size: 10,
        }
    });

    let summaryTotalInternal = 0;
    let summaryTotalExternal = 0;
    
    for (let group in groups) {
        let employeeExternalTotalPayed = 0;
        let employeeInternalTotalPayed = 0;
        
        let externalKeys = Object.keys(groups[group].external);
        let externalKeysLength = externalKeys.length;
        let internalKeys = Object.keys(groups[group].internal);
        let internalKeysLength = internalKeys.length;

        if (externalKeysLength > internalKeysLength) {
            externalKeys.forEach((key, index) => {
                let row = {};
                let rowTotalPayed = 0;
                
                if (index == 0) {
                    row.employee = groups[group].employee;
                    row.position = groups[group].position;
                } else {
                    row.employee = '';
                    row.position = '';
                }

                let internalCard = internalKeys[index];

                if (internalCard !== undefined) {
                    row.internalCard = internalCard;
                    let internalPayed = Number(getCardPayed(groups[group].internal[internalCard]));
                    row.internalPayed = internalPayed;
                    row.internalPercent = Number(numberFormat(getPayedPercent(internalPayed)));
                    employeeInternalTotalPayed += internalPayed;
                    rowTotalPayed += internalPayed;
                    summaryTotalInternal += internalPayed;
                } else {
                    row.internalCard = '';
                    row.internalPayed = '';
                    row.internalPercent = '';
                }

                row.externalCard = key;
                let externalPayed = Number(getCardPayed(groups[group].external[key]));
                row.externalPayed = externalPayed;
                row.externalPercent = Number(numberFormat(getPayedPercent(externalPayed)));
                employeeExternalTotalPayed += externalPayed;
                rowTotalPayed += externalPayed;
                summaryTotalExternal += externalPayed;

                //totals
                row.totalPayed = rowTotalPayed;
                row.totalPercent = Number(numberFormat(getPayedPercent(rowTotalPayed)));

                worksheet.addRow(row);
            })
        } else {
            internalKeys.forEach((key, index) => {
                let row = {};
                let rowTotalPayed = 0;
                
                if (index == 0) {
                    row.employee = groups[group].employee;
                    row.position = groups[group].position;
                } else {
                    row.employee = '';
                    row.position = '';
                }

                let externalCard = externalKeys[index];

                if (externalCard !== undefined) {
                    row.externalCard = externalCard;
                    let externalPayed = Number(getCardPayed(groups[group].external[externalCard]));
                    row.externalPayed = externalPayed;
                    row.externalPercent = Number(numberFormat(getPayedPercent(externalPayed)));
                    employeeExternalTotalPayed += externalPayed;
                    rowTotalPayed += externalPayed;
                    summaryTotalExternal += externalPayed;
                } else {
                    row.externalCard = '';
                    row.externalPayed = '';
                    row.externalPercent = '';
                }

                row.internalCard = key;
                let internalPayed = Number(getCardPayed(groups[group].internal[key]));
                row.internalPayed = internalPayed;
                row.internalPercent = Number(numberFormat(getPayedPercent(internalPayed)));
                employeeInternalTotalPayed += internalPayed;
                rowTotalPayed += internalPayed;
                summaryTotalInternal += internalPayed;

                //totals
                row.totalPayed = rowTotalPayed;
                row.totalPercent = Number(numberFormat(getPayedPercent(rowTotalPayed)));

                worksheet.addRow(row);
            })
        }

        let employeeTotalPayed = Number(employeeExternalTotalPayed + employeeInternalTotalPayed);

        let totalRow = {
            employee: __('Итого:'),
            position: "",
            externalCard: "",
            externalPayed: employeeExternalTotalPayed,
            externalPercent: Number(numberFormat(getPayedPercent(employeeExternalTotalPayed))),
            internalCard: "",
            internalPayed: employeeInternalTotalPayed,
            internalPercent: Number(numberFormat(getPayedPercent(employeeInternalTotalPayed))),
            totalPayed: employeeTotalPayed,
            totalPercent: Number(numberFormat(getPayedPercent(employeeTotalPayed))),
        };

        worksheet.addRow(totalRow);
        worksheet.lastRow.eachCell(cell => {
            cell.border = {
                bottom: BORDER_STYLE,
            }
            cell.fill = {
                type: 'pattern',
                pattern:'solid',
                fgColor: {
                    argb: 'cccccc',
                }
            };
        });
    }

    let summaryTotalPayed = Number(summaryTotalExternal + summaryTotalInternal);
    let summaryRow = {
        employee: __('Итого:'),
        position: "",
        externalCard: "",
        externalPayed: summaryTotalExternal,
        externalPercent: Number(numberFormat(getPayedPercent(summaryTotalExternal))),
        internalCard: "",
        internalPayed: summaryTotalInternal,
        internalPercent: Number(numberFormat(getPayedPercent(summaryTotalInternal))),
        totalPayed: summaryTotalPayed,
        totalPercent: Number(numberFormat(getPayedPercent(summaryTotalPayed))),
    };
    worksheet.addRow(summaryRow);
    let summaryRowCells = worksheet.lastRow._cells;
    let fillStyle = {
        type: 'pattern',
        pattern:'solid',
    }
    summaryRowCells.forEach((item, index) => {
        let cell = worksheet.getCell(item._address);
        cell.border = {
            bottom: BORDER_STYLE,
        }
        cell.font = {
            bold: true,
        }

        if ('totalPayed' === worksheet.columns[index].key) {
            cell.fill = {
                ...fillStyle,
                fgColor: {
                    argb: 'ffff00'
                }
            };
        }

        if ('totalPercent' === worksheet.columns[index].key) {
            cell.fill = {
                ...fillStyle,
                fgColor: {
                    argb: '00ff00'
                }
            };
        }
    });

    return Promise.resolve(workbook);
}