import * as IncomeBuilder from '@/services/report/income-builder';
import * as TimeMapper from '@/services/report/time-mapper';
import {numberFormat} from '@/services/format';
import CONSTANTS from '@/constants';

const ROW_TITLES = {
    sessions: __('Кол-во рабочих смен'),
    progression: __('Перевыполнение / (отставание) от плана'),
    is_first: __('Кол-во первичных'),
    repeated: __('Кол-во повторных'),
    avg_check: __('Средний чек (выручка / кол-во превичных п.)'),
    treatments: __('Кол-во лечений'),
};

export default (payments, appointments, filters, daySheets, doctorPlans) => {
    let results = IncomeBuilder.prepareData(payments);
    results = IncomeBuilder.addMissingAppointmentColumns(results, appointments);
    let tableData = [];
    let months = TimeMapper.getMonthData(filters);

    let monthKeys = Object.keys(months);
    monthKeys.forEach(month => {
        let monthStart = months[month].month_start >= filters.date_start ? months[month].month_start : filters.date_start;
        let monthEnd = months[month].month_end <= filters.date_end ? months[month].month_end : filters.date_end;
        months[month]['weeks'] = TimeMapper.getWeeks(monthStart, monthEnd);
    });

    let specializationKeys = Object.keys(results).sort();
    let summaryIncome = {
        name: __('ИТОГО'),
        sub_title: '',
        total_planned: 0,
        total_fact: 0,
    };
    let summarySessions = {
        sub_title: ROW_TITLES.sessions,
        total_planned: 0,
        total_fact: 0,
    };
    let summaryProgression = {
        sub_title: ROW_TITLES.progression,
    };
    let summaryIsFirst = {
        sub_title: ROW_TITLES.is_first,
        total_planned: 0,
        total_fact: 0,
    };
    let summaryRepeated = {
        sub_title: ROW_TITLES.repeated,
        total_planned: 0,
        total_fact: 0,
    };
    let summaryAvgCheck = {
        sub_title: ROW_TITLES.avg_check,
        total_planned: 0,
        total_fact: 0,
    };
    let summaryTreatments = {
        sub_title: ROW_TITLES.treatments,
        total_planned: 0,
        total_fact: 0,
    };
    
    specializationKeys.forEach(spec => {
        if (spec == 'Аванс') {
            return;
        }

        let doctors = results[spec].doctors;
        Object.keys(doctors).forEach(docId => {
            if (isNaN(docId)) {
                return;
            }
            
            let plan = doctorPlans.find(row => {
                return row.employee_id == docId
                    && row.specialization == spec
                    && row.plan_service_mark == null;
            });
            
            let moneyRow = {
                name: doctors[docId][0].doctor_name,
                id: docId,
                sub_title: spec,
                total_planned: 0,
                total_fact: 0,
            };
            let sessionsRow = {
                sub_title: ROW_TITLES.sessions,
                total_planned: 0,
                total_fact: 0,
            };
            let progressionRow = {
                sub_title: ROW_TITLES.progression,
                total_planned: 0,
                total_fact: 0,
            };
            let isFirstRow = {
                sub_title: ROW_TITLES.is_first,
                total_planned: 0,
                total_fact: 0,
            };
            let repeatedRow = {
                sub_title: ROW_TITLES.repeated,
                total_planned: 0,
                total_fact: 0,
            };
            let avgCheckRow = {
                sub_title: ROW_TITLES.avg_check,
                total_planned: 0,
                total_fact: 0,
            };
            let treatmentsRow = {
                sub_title: ROW_TITLES.treatments,
                total_planned: 0,
                total_fact: 0,
            };
            
            monthKeys.forEach(month => {
                summaryIncome[`plan_income-${month}`] = summaryIncome[`plan_income-${month}`] || 0;
                summaryIncome[`fact_income-${month}`] = summaryIncome[`fact_income-${month}`] || 0;
                summarySessions[`plan_income-${month}`] = summarySessions[`plan_income-${month}`] || 0;
                summarySessions[`plan_avg_by_session-${month}`] = summarySessions[`plan_avg_by_session-${month}`] || 0;
                summarySessions[`fact_income-${month}`] = summarySessions[`fact_income-${month}`] || 0;
                summaryIsFirst[`fact_income-${month}`] = summaryIsFirst[`fact_income-${month}`] || 0;
                summaryRepeated[`fact_income-${month}`] = summaryRepeated[`fact_income-${month}`] || 0;
                summaryTreatments[`fact_income-${month}`] = summaryTreatments[`fact_income-${month}`] || 0;

                let monthData = months[month];
                let periodStart = monthData.month_start < filters.date_start ? filters.date_start : monthData.month_start;
                let periodEnd = monthData.month_end > filters.date_end ? filters.date_end : monthData.month_end;
                
                let plannedSessions = daySheets.filter((daySheet) => {
                    return daySheet.day_sheet_owner_id == docId  
                        && daySheet.date >= monthData.month_start
                        && daySheet.date <= monthData.month_end;
                });

                let plannedPayments = plan ? Number(plan[month]) : 0;
                let periodPayments = getPeriodPayments(doctors[docId], periodStart, periodEnd);
                let periodAppointments = filterAppointments(appointments, docId, spec, periodStart, periodEnd);
                let monthFactIncome = getFactIncomes(periodPayments);
                let planAvgBySession = plan ? numberFormat((plannedPayments / plannedSessions.length)) : 0;
                let plannedSessionsCount = plannedSessions.length;
                let factMonthSessions = getFactSessions(plannedSessions, periodStart, periodEnd);
                let factMonthSessionsCount = factMonthSessions.length;
                let isFirstCount = IncomeBuilder.getPatientVisitType(periodAppointments);
                let repeatedCount = IncomeBuilder.getPatientVisitType(periodAppointments, 0);

                //Month money row
                moneyRow[`plan_income-${month}`] = plannedPayments;
                moneyRow[`plan_avg_by_session-${month}`] = planAvgBySession;
                moneyRow[`fact_income-${month}`] = numberFormat(monthFactIncome);
                moneyRow['total_planned'] += plannedPayments;
                moneyRow['total_fact'] += monthFactIncome;
                
                //Month session row
                sessionsRow[`plan_income-${month}`] = plannedSessionsCount;
                sessionsRow[`plan_avg_by_session-${month}`] = factMonthSessionsCount;
                sessionsRow[`fact_income-${month}`] = factMonthSessionsCount;

                //Month progression row
                let monthProgression = monthFactIncome - (planAvgBySession * factMonthSessionsCount);
                progressionRow[`fact_income-${month}`] = numberFormat(monthProgression);
                progressionRow['total_fact'] += monthProgression;

                //Month is first row
                isFirstRow[`fact_income-${month}`] = isFirstCount;
                isFirstRow['total_fact'] += isFirstCount;

                //Month repeated row
                repeatedRow[`fact_income-${month}`] = repeatedCount;
                repeatedRow['total_fact'] += repeatedCount;

                //Month average check for is first patients
                avgCheckRow[`fact_income-${month}`] = getIsFirstAverageCheck(monthFactIncome, isFirstCount);

                //Month treatments row
                let monthTreatments = IncomeBuilder.getTreatmentTotal(periodAppointments);
                treatmentsRow[`fact_income-${month}`] = monthTreatments;
                treatmentsRow['total_fact'] += monthTreatments;

                //Summary rows
                summaryIncome[`plan_income-${month}`] += plannedPayments;
                summaryIncome[`fact_income-${month}`] += monthFactIncome;
                summaryIncome['total_planned'] += plannedPayments;
                summaryIncome['total_fact'] += monthFactIncome;

                summarySessions[`plan_income-${month}`] += plannedSessionsCount;
                summarySessions[`plan_avg_by_session-${month}`] += factMonthSessionsCount;
                summarySessions[`fact_income-${month}`] += factMonthSessionsCount;
                summarySessions['total_planned'] += plannedSessionsCount;
                summarySessions['total_fact'] += factMonthSessionsCount;

                summaryIsFirst[`fact_income-${month}`] += isFirstCount;
                summaryIsFirst['total_fact'] += isFirstCount;

                summaryRepeated[`fact_income-${month}`] += repeatedCount;
                summaryRepeated['total_fact'] += repeatedCount;

                summaryTreatments[`fact_income-${month}`] += monthTreatments;
                summaryTreatments['total_fact'] += monthTreatments;

                //Seed weeks data
                monthData.weeks.forEach((week, index) => {
                    let weekStart = TimeMapper.formatDate(week.start);
                    let weekEnd = TimeMapper.formatDate(week.end);
                    let weekPayments = getPeriodPayments(periodPayments, weekStart, weekEnd);
                    let weekIncome = getFactIncomes(weekPayments);
                    let weekSessions = getFactSessions(factMonthSessions, weekStart, weekEnd);
                    let weekAppointments = filterAppointmentsByPeriod(periodAppointments, weekStart, weekEnd);
                    let weekIsFirstCount = IncomeBuilder.getPatientVisitType(weekAppointments);
                    let weekRepeatedCount = IncomeBuilder.getPatientVisitType(weekAppointments, 0);

                    moneyRow[`fact_week-${month}-${index}`] = weekIncome;
                    sessionsRow[`fact_week-${month}-${index}`] = weekSessions.length;
                    progressionRow[`fact_week-${month}-${index}`] = numberFormat(weekIncome - (planAvgBySession * weekSessions.length));
                    isFirstRow[`fact_week-${month}-${index}`] = weekIsFirstCount;
                    repeatedRow[`fact_week-${month}-${index}`] = weekRepeatedCount;
                    avgCheckRow[`fact_week-${month}-${index}`] = getIsFirstAverageCheck(weekIncome, weekIsFirstCount);
                    treatmentsRow[`fact_week-${month}-${index}`] = IncomeBuilder.getTreatmentTotal(weekAppointments);
                });
            });

            avgCheckRow['total_fact'] = numberFormat(isFirstRow['total_fact'] > 0 
                ? (moneyRow['total_fact'] / isFirstRow['total_fact']) 
                : 0
            );
            progressionRow['total_fact'] = numberFormat(progressionRow['total_fact']);

            tableData.push(moneyRow);
            tableData.push(sessionsRow);
            tableData.push(progressionRow);
            tableData.push(isFirstRow);
            tableData.push(repeatedRow);
            tableData.push(avgCheckRow);
            tableData.push(treatmentsRow);
        });
    });

    tableData.push(summaryIncome);
    tableData.push(summarySessions);
    tableData.push(summaryProgression);
    tableData.push(summaryIsFirst);
    tableData.push(summaryRepeated);
    tableData.push(summaryAvgCheck);
    tableData.push(summaryTreatments);
       
    return {
        tableData,
        months
    };
}

const getPeriodPayments = (payments, periodStart, periodEnd) => {
    return payments.filter(payment => {
        return payment.created_at >= periodStart
            && payment.created_at <= periodEnd;
    });
}

const filterAppointments = (appointments, docId, spec, periodStart, periodEnd) => {
    return filterAppointmentsByPeriod(
        IncomeBuilder.filterAppointments(appointments, docId, spec), 
        periodStart, 
        periodEnd
    );
}

const filterAppointmentsByPeriod = (appointments, periodStart, periodEnd) => {
    return appointments.filter(appointment => {
        return appointment.date >= periodStart && appointment.date <= periodEnd;
    });
}

const getFactSessions = (plannedDaySheets, periodStart, periodEnd) => {
    return plannedDaySheets.filter(row => {
        return row.date >= periodStart && row.date <= periodEnd;
    });
}

const getIsFirstAverageCheck = (income, isFirstCount) => {
    return numberFormat((income != 0 && isFirstCount != 0) 
        ? (income / isFirstCount) 
        : 0
    );
}

const getFactIncomes = (payments) => {
    return payments.reduce((total, row) => {
        if (row.type === CONSTANTS.PAYMENT.TYPES.INCOME) {
            total += Number(row.payed_amount);
        }
        if (row.type === CONSTANTS.PAYMENT.TYPES.EXPENSE) {
            total -= Number(row.payed_amount);
        }
        return total;
    }, 0);
}