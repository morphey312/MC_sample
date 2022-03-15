import Excel from 'exceljs';
import {numberFormat} from '@/services/format';
import CONSTANTS from '@/constants';

const UZI = __('УЗИ');
const EKG = __('ЭКГ');
const ENK = __('ЭНК');
const END = __('ЭНД');
const EEG = __('ЭЭГ');
const SINGLE_TIME_SPEC = [UZI, EKG, ENK, END, EEG];
const BONUS_PERIOD = 30;
const BONUS_PERCENT = 10;
const BONUS_PERCENT_INCREASED = 50;
const SINGLE_TIME_SPECIALIZATION_ID = [12];
const SKIP_SERVICE_IDS = [155, 868, 1244, 1269, 1927, 2093, 2318, 2711, 2908, 2975, 3070, 3165, 3166, 3314, 3315, 3404];
const SKIP_SERVICE_UZI = [330, 331, 332, 333, 335, 596, 650, 654, 676, 677, 886, 887, 1070, 1071, 1277, 1278, 1279, 1280, 
1281, 2034, 2020, 2688, 2689, 2690, 3171, 3172, 3420, 3508, 3509, 3795, 3796, 3876, 4130];
const SKIP_UZI_SPECIALIZATIONS = [2, 5];
const INCREASED_PERCENT_SPECIALIZATION = 4;
const SKIP_INCREASED_PERCENT_SPECIALIZATION = [3, 12, 17, 40, 48, 63];
const MILLISECONDS_PER_DAY = 86400000;

const getExternalPeriodPatients = (data, sources) => {
    for (let source in sources) {
        sources[source].external = groupList(findExternalSourceAppointments(data, source));
    }
    return sources;
}

const getInternalPeriodPatients = (data, sources) => {
    for (let source in sources) {
        sources[source].internal = groupList(findInternalSourceAppointments(data, source));
    }
    return sources;
}

const findInternalSourceAppointments = (data, sourceId) => {
    return data.filter(row => {
        return (row.specialization_visit_source == sourceId && (_.isVoid(row.appointment_source_id)) || row.appointment_source_id == sourceId);
    });
}

const findExternalSourceAppointments = (data, source) => {
    return data.filter(row => {
        return source == row.redirect_source.source_id && 
                (_.isVoid(row.patient_visit_date) || dayDifference(row.date, row.patient_visit_date) <= BONUS_PERIOD);
    });
}

const groupList = (list, groupField = 'specializationCard') => {
    return _.groupBy(list, groupField);
}

const dayDifference = (dateFrom, dateTo) => {  
    let timeDiff = Math.abs(new Date(dateFrom).getTime() - new Date(dateTo).getTime());
    return Math.ceil(timeDiff / MILLISECONDS_PER_DAY);
}

const getSources = (data) => {
    let sources = {};

    data.forEach(row => {
        if (row.redirect_source == null || row.skip_redirect) {
            return;
        }

        let patientSource = row.redirect_source.source_id;

        if (row.appointment_source_id == null && 
            SINGLE_TIME_SPECIALIZATION_ID.indexOf(row.specialization_id) != -1 
            && sources[patientSource] !== undefined) {
            return;
        }

        if (_.isFilled(row.redirect_source.employee.employee_id) && sources[patientSource] == undefined) {
            sources[patientSource] = {
                name: row.redirect_source.source_name,
                employee: row.redirect_source.employee.full_name,
                employee_id: row.redirect_source.employee.employee_id,
                position: getPosition(row.redirect_source.employee.positions),
                clinic_id: row.clinic_id,
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

const getCardPayed = (appointments) => {
    return appointments.reduce((payed, row) => {
        return payed + Number(row.services.reduce((total, service) => {
            return total + Number(service.payed);
        }, 0));
    }, 0);
}

const getPayedPercent = (payed, bonusPercent = BONUS_PERCENT) => {
    if (bonusPercent === BONUS_PERCENT_INCREASED) {
        payed = (payed == 0) ? 0 : (payed / 100) * BONUS_PERCENT_INCREASED;
        return (payed == 0) ? 0 : (payed / 100) * BONUS_PERCENT;
    }
    return (payed == 0) ? 0 : (payed / 100) * bonusPercent;
}

const sourceHasIncreasedSpecialization = (appointment) => {
    return _.get(appointment, 'redirect_source.employee.specializations', []).indexOf(INCREASED_PERCENT_SPECIALIZATION) !== -1;
    /**
     * Part of same specialization changes, not approved
     * let employeeSpecializations = _.get(appointment, 'redirect_source.employee.specializations', []);
     * return employeeSpecializations.some(spec => SKIP_INCREASED_PERCENT_SPECIALIZATION.indexOf(spec) === -1);
     */
}

const getInternalPayedPercent = (appointments, internalPayed) => {
    let increasedList = appointments.filter(row => {
        /**
         * Part of same specialization changes, not approved
         * if (SKIP_INCREASED_PERCENT_SPECIALIZATION.indexOf(row.specialization_id) !== -1 || 
         * SKIP_INCREASED_PERCENT_SPECIALIZATION.indexOf(row.card_specialization_id) !== -1) {
         */
        
        if (INCREASED_PERCENT_SPECIALIZATION !== row.specialization_id || INCREASED_PERCENT_SPECIALIZATION !== row.card_specialization_id) {
            return false;
        }
        if (_.isFilled(row.source_employee_id) && 
            row.doctor_type === CONSTANTS.DAY_SHEET.OWNER_TYPES.EMPLOYEE && 
            row.source_employee_id != row.doctor_id &&
            sourceHasIncreasedSpecialization(row)) {
            return true;
        }
        return false;
    }).map(row => row.id);
    
    if (increasedList.length == 0) {
        return getPayedPercent(internalPayed);
    }
    
    let percents = 0;
    appointments.forEach(appointment => {
        let percentType = BONUS_PERCENT;
        if (increasedList.indexOf(appointment.id) !== -1) {
            percentType = BONUS_PERCENT_INCREASED;
        }
        percents += getPayedPercent(
            appointment.services.reduce((total, service) => {
                return total + Number(service.payed);
            }, 0),
            percentType);
    });
    return percents;
}

const getInternalAppointments = (data, filters) => {
    let list = [];
    let dateStart = filters.date_start;
    let repeatedAppointments = data.filter(item => {
        return (item.is_first == 0) && (item.date >= dateStart);
    });

    data.forEach(row => {
        if (row.skip_redirect) {
            return;
        }

        if (_.isFilled(row.appointment_source_id)) {
            if (row.is_first == 1) {
                let appointments = filterInternalAppointments(repeatedAppointments, row);
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

const filterInternalAppointments = (data, row) => {
    return data.filter(item => {
        return (dayDifference(item.date, row.date) <= BONUS_PERIOD) &&
               (item.id != row.id) && 
               (row.card_specialization_id === item.card_specialization_id) &&
               (row.patient_id === item.patient_id) &&
               _.isVoid(row.appointment_source_id);
    });
}

const addInternalAppointment = (list, row) => {
    let index = list.findIndex(item => item.id == row.id);
    if (index === -1) {
        list.push(row);
    }
    return list;
}

const shouldSkipService = (service) => {
    return SKIP_SERVICE_IDS.indexOf(service.service_id) !== -1 || service.payed == 0;
}

const skipSpecializationService = (service, row, isInternal = false) => {
    if (!isInternal) {
        return false;
    }
    if (_.isFilled(row.appointment_source_id) && SKIP_UZI_SPECIALIZATIONS.indexOf(row.card_specialization_id) !== -1) {
        return SKIP_SERVICE_UZI.indexOf(service.service_id) !== -1;
    }
    return false;
}

const filterActualPayments = (row, dateStart, dateEnd, isInternal = false) => {
    let services = row.services.filter(service => {
        if (shouldSkipService(service) || skipSpecializationService(service, row, isInternal)) {
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
            if (payment.type === CONSTANTS.PAYMENT.TYPES.INCOME) {
                sum += Number(payment.payed_amount);
            } else if (payment.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
                sum -= Number(payment.payed_amount);
            }
            return sum;
        }, 0);
        return service;
    })
    row.services = services;
    return row;
}

const filterPayments = (list, filters, isInternal = false) => {
    let dateStart = filters.date_start;
    let dateEnd = filters.date_end;
    
    return list.filter(row => {
        let appointment = filterActualPayments(row, dateStart, dateEnd, isInternal);
        if (appointment) {
            row = appointment;
            return true;
        }
        return false;
    });
}

const getTableData = (groups) => {
    let tableData = [];
    let summaryTotalInternal = 0;
    let summaryTotalExternal = 0;
    let summaryInternalPercent = 0;
    
    for (let group in groups) {
        let externalKeys = Object.keys(groups[group].external);
        let externalKeysLength = externalKeys.length;
        let internalKeys = Object.keys(groups[group].internal);
        let internalKeysLength = internalKeys.length;

        if (internalKeysLength == 0 && externalKeysLength == 0) {
            continue;
        }

        let employeeExternalTotalPayed = 0;
        let employeeInternalTotalPayed = 0;
        let employeeInternalPercent = 0;

        if (externalKeysLength > internalKeysLength) {
            externalKeys.forEach((key, index) => {
                let row = {};
                
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
                    let internalPercent = getInternalPayedPercent(groups[group].internal[internalCard], internalPayed);
                    row.internalPercent = Number(numberFormat(internalPercent));
                    employeeInternalTotalPayed += internalPayed;
                    summaryTotalInternal += internalPayed;
                    employeeInternalPercent += internalPercent;
                    summaryInternalPercent += internalPercent;
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
                summaryTotalExternal += externalPayed;

                //totals
                row.isEmployeeSummary = false;
                tableData.push(row);
            })
        } else {
            internalKeys.forEach((key, index) => {
                let row = {};
                
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
                    summaryTotalExternal += externalPayed;
                } else {
                    row.externalCard = '';
                    row.externalPayed = '';
                    row.externalPercent = '';
                }

                row.internalCard = key;
                let internalPayed = Number(getCardPayed(groups[group].internal[key]));
                row.internalPayed = internalPayed;
                let internalPercent = getInternalPayedPercent(groups[group].internal[key], internalPayed);
                row.internalPercent = Number(numberFormat(internalPercent));
                employeeInternalTotalPayed += internalPayed;
                summaryTotalInternal += internalPayed;
                employeeInternalPercent += internalPercent;
                summaryInternalPercent += internalPercent;
                //totals
                row.isEmployeeSummary = false;

                tableData.push(row);
            })
        }

        let employeeTotalPayed = Number(employeeExternalTotalPayed + employeeInternalTotalPayed);
        let employeeTotalPercent = Number(numberFormat(getPayedPercent(employeeExternalTotalPayed) + employeeInternalPercent));
        let employeeExternalPercent = Number(numberFormat(getPayedPercent(employeeExternalTotalPayed)));
        employeeInternalPercent = Number(numberFormat(employeeInternalPercent));
        
        let totalRow = {
            employee: __('Итого:'),
            position: "",
            externalCard: "",
            externalPayed: employeeExternalTotalPayed,
            externalPercent: employeeExternalPercent,
            internalCard: "",
            internalPayed: employeeInternalTotalPayed,
            internalPercent: employeeInternalPercent,
            totalPayed: employeeTotalPayed,
            totalPercent: employeeTotalPercent,
            isEmployeeSummary: true,
        };
        tableData.push(totalRow);
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
        internalPercent: Number(numberFormat(summaryInternalPercent)),
        totalPayed: summaryTotalPayed,
        totalPercent: Number(numberFormat(getPayedPercent(summaryTotalExternal) + summaryInternalPercent)),
        isTableSummary: true,
    };
    tableData.push(summaryRow);
    return tableData;
}

const getChartData = (groups) => {
    let employeeData = [];
    
    for (let group in groups) {
        let externalKeys = Object.keys(groups[group].external);
        let externalKeysLength = externalKeys.length;
        let internalKeys = Object.keys(groups[group].internal);
        let internalKeysLength = internalKeys.length;

        if (internalKeysLength == 0 && externalKeysLength == 0) {
            continue;
        }

        let row = {};
        let employeeExternalTotalPayed = 0;
        let employeeInternalTotalPayed = 0;
        let employeeInternalPercent = 0;

        if (externalKeysLength > internalKeysLength) {
            externalKeys.forEach((key, index) => {
                if (index == 0) {
                    row['name'] = groups[group].employee;
                    row['clinic_id'] = groups[group].clinic_id;
                }

                let internalCard = internalKeys[index];

                if (internalCard !== undefined) {
                    let internalPayed = Number(getCardPayed(groups[group].internal[internalCard]));
                    let internalPercent = getInternalPayedPercent(groups[group].internal[internalCard], internalPayed);
                    employeeInternalTotalPayed += internalPayed;
                    employeeInternalPercent += internalPercent;
                }

                let externalPayed = Number(getCardPayed(groups[group].external[key]));
                employeeExternalTotalPayed += externalPayed;
            })
        } else {
            internalKeys.forEach((key, index) => {
                if (index == 0) {
                    row['name'] = groups[group].employee;
                    row['clinic_id'] = groups[group].clinic_id;
                }

                let externalCard = externalKeys[index];

                if (externalCard !== undefined) {
                    let externalPayed = Number(getCardPayed(groups[group].external[externalCard]));
                    employeeExternalTotalPayed += externalPayed;
                }

                let internalPayed = Number(getCardPayed(groups[group].internal[key]));
                let internalPercent = getInternalPayedPercent(groups[group].internal[key], internalPayed);
                employeeInternalTotalPayed += internalPayed;
                employeeInternalPercent += internalPercent;
            })
        }

        let employeeTotalPayed = Number(employeeExternalTotalPayed + employeeInternalTotalPayed);
        let employeeTotalPercent = Number(numberFormat(getPayedPercent(employeeExternalTotalPayed) + employeeInternalPercent));
        let employeeExternalPercent = Number(numberFormat(getPayedPercent(employeeExternalTotalPayed)));
        employeeInternalPercent = Number(numberFormat(employeeInternalPercent));
        
        row['internal'] = {
            payed: employeeInternalTotalPayed,
            percent: employeeInternalPercent,
        };
        row['external'] = {
            payed: employeeExternalTotalPayed,
            percent: employeeExternalPercent,
        };
        row['summary'] = {
            payed: employeeTotalPayed,
            percent: employeeTotalPercent,
        };
        employeeData.push(row);
    }
    return employeeData;
}

const getGroups = (data, filters) => {
    data.internal = filterPayments(data.internal, filters, true);
    data.external = filterPayments(data.external, filters);
    let internalList = addFullCards(getInternalAppointments(data.internal, filters));
    let sources = getSources([...data.external, ...data.internal]);
    let externalList = addFullCards(data.external);
    let groups = getExternalPeriodPatients(externalList, sources);
    groups = getInternalPeriodPatients(internalList, sources);
    return groups;
}

const getRedirects = (data, filters) => {
    data.internal = filterPayments(data.internal, filters, true);
    data.external = filterPayments(data.external, filters);
    let internalAppointments = getInternalAppointments(data.internal, filters);
    let sources = getSources([...data.external, ...data.internal]);
    let internalList = [];
    let externalList = [];
    for (let source in sources) {
        internalList = [...internalList, ...findInternalSourceAppointments(internalAppointments, source)];
        externalList = [...externalList, ...findExternalSourceAppointments(data.external, source)]
    }
    
    return {externalList, internalList};
}

const groupPeriods = (externalList, internalList) => {
    let externalPayments = getPeriodPayments(externalList);
    let internalPayments = getPeriodPayments(internalList);
    return [
        {
            payments: _.groupBy(externalPayments, 'month'),
        }, 
        {
            payments: _.groupBy(internalPayments, 'month'),
        }
    ];
}

const getPeriodPayments = (appointments) => {
    let results = [];
    appointments.forEach(appointment => {
        appointment.services.forEach(service => {
            results = [
                ...results, 
                ...service.payments.map(payment => {
                    let date = new Date(payment.date);
                    payment.is_internal = appointment.is_internal;
                    payment.month = `${date.getFullYear()}-${("0" + (date.getMonth() + 1)).slice(-2)}-01`;
                    return payment;
                }),
            ];
        });
    });
    return results;
}

const getFileData = (rows) => {
    const borderStyle = {style:'medium', color: {argb:'000'}};
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

    rows.forEach(row => {
        worksheet.addRow(row);
        if (row.employee == __('Итого:')) {
            worksheet.lastRow.eachCell(cell => {
                cell.border = {
                    bottom: borderStyle,
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
    });

    let summaryRowCells = worksheet.lastRow._cells;
    let fillStyle = {
        type: 'pattern',
        pattern:'solid',
    }
    summaryRowCells.forEach((item, index) => {
        let cell = worksheet.getCell(item._address);
        cell.border = {
            bottom: borderStyle,
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
    return workbook;
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

export default (data, filters, type = 'table') => {
    if (type === 'table') {
        let groups = getGroups(data, filters);
        return getTableData(groups);
    } else if (type === 'chart') {
        let groups = getGroups(data, filters);
        return getChartData(groups);
    } else if (type === 'period-chart') {
        let {externalList, internalList} = getRedirects(data, filters);
        return groupPeriods(externalList, internalList, filters);
    } else if (type === 'export') {
        return getFileData(data);
    }

    return [];
}

