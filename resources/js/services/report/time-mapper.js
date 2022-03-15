import moment from 'moment';
import {dateFormat} from '@/services/format';

const getMonthData = (filters) => {
    let months = {};
    let momentedStart = moment(filters.date_start, '', 'en');
    let momentedEnd = moment(filters.date_end, '', 'en');
    let monthCount = moment(getEndOf(momentedEnd) - getStartOf(momentedStart)).month();
    let firstMonthEnd = getEndOf(momentedStart.clone());
    
    months[getMonthName(momentedStart)] = {
        momented_start: momentedStart,
        momented_end: firstMonthEnd,
        month_start: formatDate(momentedStart),
        month_end: formatDate(firstMonthEnd),
    };

    if (monthCount != 0) {
        for (let i = 1; i < monthCount; i++) {
            let month = momentedStart.add(1, 'months');
            let monthEnd = getEndOf(month.clone());
            months[getMonthName(month)] = {
                momented_start: month,
                momented_end: monthEnd,
                month_start: formatDate(month),
                month_end: formatDate(monthEnd),
            };
        }
    }
    return months;
}

const getEndOf = (date, period = 'month') => {
    return date.endOf(period);
}

const getStartOf = (date, period = 'month') => {
    return date.startOf(period);
}

const getMonthName = (date) => {
    return date.format('MMMM').toLowerCase();
}

const getWeeks = (monthStart, monthEnd) => {
    let start;
    let end;
    let weeks = [];
    let firstDay = new Date(monthStart);
    let lastDay = new Date(monthEnd);
    let daysInMonth = lastDay.getDate();
    let dayOfWeek = firstDay.getDay();
    let year = firstDay.getFullYear();
    let month = firstDay.getMonth();
    
    for (let i = firstDay.getDate(); i < daysInMonth + 1; i++) {
        if (dayOfWeek === 0 || i === 1) {
            start = i;
        }

        if (dayOfWeek === 6 || i === daysInMonth) {
            end = i;
            if (start !== end) {
                weeks.push({
                    start: new Date(year, month, start),
                    end: new Date(year, month, end),
                });
            }
        }
        dayOfWeek = new Date(year, month, i).getDay();
    }
    return weeks;
}

const formatDate = (date, format = 'YYYY-MM-DD') => {
    return dateFormat(date, format);
}

export {
    getMonthData,
    getMonthName,
    getWeeks,
    formatDate,
    getEndOf,
    getStartOf
}