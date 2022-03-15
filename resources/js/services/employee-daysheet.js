import DaySheet from '@/models/day-sheet';
import TimeSheet from '@/models/day-sheet/time-sheet';

class EmployeeDaySheet 
{
    /**
    * Merge same date time sheets in clinic
    * 
    * @param {array} data
    * @param {string} dateField
    *
    * @returns array
    */
	castTimeSheetData(data, dateField = 'startDate') {
        let list = [];

        data.forEach((row) => {
            let index = _.findIndex(list, (item) => {
                return item[dateField] == row[dateField] && item.clinic_id == row.clinic_id;
            });

            if (index === -1) {
                // Create new daysheet if row not in result list 
                let daySheet = new DaySheet(row);
                list.push(daySheet);
            } else {
                if (list[index].clinic_id == row.clinic_id) {
                    row.time_sheets.forEach((time_row) => {
                        // Find result list item time_sheet
                        let sameTimeSheet = _.find(list[index].time_sheets, (time_sheet) => {
                            let from = `${time_sheet.time_from}:00`;
                            let to = `${time_sheet.time_to}:00`;

                            return from == time_row.time_from && to == time_row.time_to;
                        });
                        
                        // Merge same time_sheet attributes
                        if (sameTimeSheet) {
                            sameTimeSheet.specialization_data = {...sameTimeSheet.specialization_data, ...time_row.specialization_data};
                            sameTimeSheet.specialization_names = [...sameTimeSheet.specialization_names, ...time_row.specialization_names];
                            sameTimeSheet.specializations = [...sameTimeSheet.specializations, ...time_row.specializations];
                        } else {
                        // Merge time_sheets if start&end not found
                            list[index].time_sheets = [...list[index].time_sheets, new TimeSheet(time_row)];
                        }
                    });
                }
            }
        });
        
        return list;
    }
}

export default EmployeeDaySheet;