import Excel from 'exceljs';
import moneyFormatter from '@/services/money-formatter';
import {dateFormat, numberFormat, listFormat} from '@/services/format';
import moment from 'moment';
import CONSTANTS from '@/constants';

const MonthsShort = [__('Січ'), __('Лют'), __('Бер'), __('Кві'), __('Тра'), __('Чер'), __('Лип'), __('Сер'), __('Вер'), __('Жов'), __('Лис'), __('Гру')];
const localMoment = moment();
localMoment.locale('uk', {
    months: [__('січня'), __('лютого'), __('березня'), __('квітня'), __('травня'), __('червня'), __('липня'), __('серпня'), __('вересня'), __('жовтня'), __('листопада'), __('грудня')],
    monthsShort: MonthsShort,
});

const ALIGNMENT = {vertical: 'middle', wrapText: true, horizontal: 'center'};
const COLUMN_STYLE = {
    alignment: ALIGNMENT,
    font: {
        size: 14,
    }
};
const REGULAR_FONT = {
    name: 'Arial',
    size: 14,
};
const SUBTITLE_FONT = {
    name: 'Arial',
    size: 16,
};
const TITLE_FONT = {
    name: 'Arial',
    size: 20,
    bold: true,
};
const ROW_NUM_START = 38;
const APPROVE = __('ЗАТВЕРДЖУЮ');
const SIGNER_NOTE = __('* Відповідальний за здійснення господарської операції і правильність її оформлення');
const FROM_CLINIC = __('Від Виконавця*');
const FROM_INSURER = __('Від Замовника');
const EGRPO = 'egrpo';
const LETTER_A = 'A';
const LETTER_C = 'C';
const LETTER_E = 'E';
const LETTER_G = 'G';
const LETTER_K = 'K';
const LETTER_H = 'H';
const LETTER_I = 'I';
const LETTER_J = 'J';
const LEFT_LETTER_START = LETTER_A;
const LEFT_LETTER_END = LETTER_E;
const RIGHT_LETTER_START = LETTER_G;
const RIGHT_LETTER_END = LETTER_K;
const INDENT = {indent: 2};
const COMMON_FORMAT = {vertical: 'bottom', horizontal: 'left', wrapText: true};
const LEFT_FORMAT = {...COMMON_FORMAT, ...INDENT};
const ALIGN_TOP_RIGHT = {vertical: 'top', horizontal: 'right'};
const DOC_TITLE = __('АКТ надання послуг');
const BORDER_BOTTOM_MEDIUM = {
    bottom: {style: 'medium'},
};
const BORDER_LIGHT_STYLES = {style: 'thin', color: {argb: 'FF000000'}};
const BORDER_AROUND = {
    top: BORDER_LIGHT_STYLES,
    left: BORDER_LIGHT_STYLES,
    bottom: BORDER_LIGHT_STYLES,
    right: BORDER_LIGHT_STYLES
};

const getPreamble = (clinic = {}, insurer = {}) => {
    return `
Ми, що нижче підписалися, представник Замовника ${getModelField(insurer, 'name')} ${getModelField(insurer, 'signer_position')} ${getModelField(insurer, 'signer')},
з одного боку, і представник Виконавця ${getModelField(clinic, 'official_name')} ${getModelField(clinic, 'signer_position')} ${getModelField(clinic, 'signer')},
з іншого боку, склали цей акт про те, що на підставі наведених документів:
            Договір: ${getAgreement(clinic.id, insurer)}
Виконавцем були виконані наступні роботи (надані такі послуги):`;
}

const getAgreement = (clinicId, insurer) => {
    let clinic = null;
    if (insurer.company_clinics) {
        clinic = insurer.company_clinics.find(item => item.clinic_id == clinicId);
    }
    return clinic ? clinic.agreement : '';
}

const getDocTotalsSummary = (amount, tax = 0) => {
    let amountString = moneyFormatter.moneyToString(amount);
    let taxString = moneyFormatter.moneyToString(tax);
    return `Загальна вартість робіт (послуг) склала без ПДВ ${amountString}, ПДВ ${taxString}, загальна вартість робіт (послуг) із ПДВ ${amountString}.
Замовник претензій по об'єму, якості та строкам виконання робіт(надання послуг) не має.
Місце складання: `;
}

const getTotal = (rows = []) => {
    return rows.reduce((total, row) => {
        let debt = 0;
        row.cost = Number(row.cost);
        if (row.franchise == 0) {
            debt = row.cost;
        } else {
            debt = row.cost - (row.cost / 100 * Number(row.franchise));
        }
        return total + debt;
    }, 0);
}

const getFormatedAmount = (price = '', notRound = false) => {
    if (notRound) {
        return Number(price).toFixed(2);
    }
    return Math.round(Number(price));
}

const getServiceName = (service) => {
    if (service.container_type === CONSTANTS.APPOINTMENT_SERVICE.CONTAINERS.ANALYSES
        && service.analysis_names && service.analysis_names.length != 0) {
        return service.analysis_names.join(', ');
    }
    return service.service_name_ua;
}

const getRows = (data = [], clinic) => {
    let rows = [];
    let groups = _.groupBy(data, 'patient_name');
    let groupKeys = Object.keys(groups).sort();

    groupKeys.forEach(patient => {
        groups[patient].forEach((item, index) => {
            let appointmentDate = moment(item.appointment_date).locale('uk', {monthsShort: MonthsShort});
            let row = [
                patient,
                item.policy_number,
                item.patient_card,
                appointmentDate.format('DD.MM.YYYY'),
                getFormatedAmount(numberFormat(item.cost), clinic.not_round_cost),
                getFormatedAmount(item.franchise, clinic.not_round_cost),
                getFormatedAmount(item.payed, clinic.not_round_cost),
                item.quantity,
                getServiceName(item),
                listFormat(item.diagnosis),
            ];

            if (index === (groups[patient].length - 1)) {
                row.push(getFormatedAmount(getTotal(groups[patient]), clinic.not_round_cost));
            } else {
                row.push('');
            }
            row.push(item.insurance_company_name, item.act_service.number);
            rows.push(row);
        });
    });
    return rows;
}

const getModelField = (model = {}, key = '') => {
    return model[key] || '______';
}

const getModelFields = (model, fields = []) => {
    let phones = ['contact_phone', 'phone_number'];
    let output = '';
    fields.forEach(field => {
        if (model[field]) {
            output += '\n';
            if (field == EGRPO) {
                output += __('код за ЄДРПОУ') + ' ' + model[field];
            } else if (phones.indexOf(field) != -1) {
                output += __('тел.:') + ' ' + model[field];
            } else {
                output += model[field];
            }
        }
    });
    return output;
}

export default (data = [], requisites = {}) => {
    console.log(data);
    let clinic = requisites.clinic || {};
    let insurer = requisites.insurer || {};
    let insurenceAct = requisites.insuranceAct || {};
    let workbook = new Excel.Workbook();
    let worksheet = workbook.addWorksheet(__('Сводная'), {
        autoFilter: false,
        pageSetup: {
            paperSize: 9,
            fitToPage: true,
        },
    });

    worksheet.columns = [
        {header: '', key: 'patient', width: 30, style: COLUMN_STYLE},
        {header: '', key: 'policy_number', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'patient_card', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'date', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'cost', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'franchise', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'payed', width: 16, style: COLUMN_STYLE},
        {header: '', key: 'quantity', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'service', width: 39, style: COLUMN_STYLE},
        {header: '', key: 'diagnosis', width: 32, style: COLUMN_STYLE},
        {header: '', key: 'total', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'insurance_company_name', width: 12, style: COLUMN_STYLE},
        {header: '', key: 'act_id', width: 12, style: COLUMN_STYLE},
    ];

    let rows = getRows(data, clinic);
    console.log('rows', rows);
    const table = worksheet.addTable({
        name: 'Act',
        ref: `${LEFT_LETTER_START}${ROW_NUM_START}`,
        headerRow: true,
        totalsRow: false,
        style: {
            theme: null,
        },
        columns: [
            {name: __('П. І. Б. Застрахованої особи')},
            {name: __('Номер поліса')},
            {name: __('Амбулаторна карта')},
            {name: __('Дата')},
            {name: __('Вартість згідно прайсу, грн.')},
            {name: __('Франшиза, %')},
            {name: __('Сплачено за франшизою, грн.')},
            {name: __('Кількість послуг')},
            {name: __('Найменування послуг згідно прайсу')},
            {name: __('Діагноз')},
            {name: __('Всього по пацієнту')},
            {name: __('Страховая компания')},
            {name: __('Номер акта')},
        ],
        rows,
    });
    table.totalsRow = false;
    table.commit();

    //Table header styles
    let tableHeaderRow = worksheet.getRow(ROW_NUM_START);
    tableHeaderRow.font = {size: 14, bold: true,};
    tableHeaderRow.height = 75;

    //Table rows styles
    let tableRowsTotal = ROW_NUM_START + table.height;
    for (let rowNum = ROW_NUM_START; rowNum <= tableRowsTotal; rowNum++) {
        let row = worksheet.getRow(rowNum);
        row.eachCell(cell => {
            cell.border = BORDER_AROUND;
        });

        if (rowNum > ROW_NUM_START) {
            row.height = 70;
        }
    }

    /**
     * Document header
     */
        //Document approve
    let leftApprove = worksheet.getCell(`${LEFT_LETTER_START}1`);
    let rightApprove = worksheet.getCell(`${RIGHT_LETTER_START}1`);
    leftApprove.value = APPROVE;
    rightApprove.value = APPROVE;
    leftApprove.alignment = LEFT_FORMAT;
    rightApprove.alignment = COMMON_FORMAT;
    worksheet.mergeCells(`${LEFT_LETTER_START}1:${LEFT_LETTER_END}4`);
    worksheet.mergeCells(`${RIGHT_LETTER_START}1:${RIGHT_LETTER_END}4`);
    let approveFont = {
        name: 'Arial',
        size: 20,
    };
    leftApprove.font = approveFont;
    rightApprove.font = approveFont;

    //Document signers positions
    let leftPosition = worksheet.getCell(`${LEFT_LETTER_START}7`);
    let rightPosition = worksheet.getCell(`${RIGHT_LETTER_START}7`);
    leftPosition.value = getModelField(clinic, 'signer_position');
    rightPosition.value = getModelField(insurer, 'signer_position');
    leftPosition.alignment = {...LEFT_FORMAT, vertical: 'top'};
    rightPosition.alignment = {...COMMON_FORMAT, vertical: 'top'};
    worksheet.mergeCells(`${LEFT_LETTER_START}7:${LEFT_LETTER_END}9`);
    worksheet.mergeCells(`${RIGHT_LETTER_START}7:${RIGHT_LETTER_END}9`);
    leftPosition.font = SUBTITLE_FONT;
    rightPosition.font = SUBTITLE_FONT;

    //Document companies
    let leftCompany = worksheet.getCell(`${LEFT_LETTER_START}10`);
    let rightCompany = worksheet.getCell(`${RIGHT_LETTER_START}10`);
    leftCompany.value = getModelField(clinic, 'official_name');
    rightCompany.value = getModelField(insurer, 'name');
    leftCompany.alignment = {...LEFT_FORMAT, vertical: 'top'};
    rightCompany.alignment = {...COMMON_FORMAT, vertical: 'top'};
    worksheet.mergeCells(`${LEFT_LETTER_START}10:${LEFT_LETTER_END}14`);
    worksheet.mergeCells(`${RIGHT_LETTER_START}10:${RIGHT_LETTER_END}14`);
    leftCompany.font = SUBTITLE_FONT;
    rightCompany.font = SUBTITLE_FONT;

    //Document signs
    let leftSign = worksheet.getCell(`${LEFT_LETTER_START}15`);
    let rightSign = worksheet.getCell(`${RIGHT_LETTER_START}15`);
    leftSign.alignment = LEFT_FORMAT;
    rightSign.alignment = COMMON_FORMAT;
    leftSign.border = BORDER_BOTTOM_MEDIUM;
    rightSign.border = BORDER_BOTTOM_MEDIUM;
    worksheet.mergeCells(`${LEFT_LETTER_START}15:${LEFT_LETTER_END}16`);
    worksheet.mergeCells(`${RIGHT_LETTER_START}15:${RIGHT_LETTER_END}16`);

    //Document signers positions
    let leftSigner = worksheet.getCell(`${LEFT_LETTER_START}17`);
    let rightSigner = worksheet.getCell(`${RIGHT_LETTER_START}17`);
    leftSigner.value = getModelField(clinic, 'signer');
    rightSigner.value = getModelField(insurer, 'signer');
    leftSigner.alignment = LEFT_FORMAT;
    rightSigner.alignment = COMMON_FORMAT;
    worksheet.mergeCells(`${LEFT_LETTER_START}17:${LEFT_LETTER_END}18`);
    worksheet.mergeCells(`${RIGHT_LETTER_START}17:${RIGHT_LETTER_END}18`);
    leftSigner.font = SUBTITLE_FONT;
    rightSigner.font = SUBTITLE_FONT;

    //Document title
    let docTitle = worksheet.getCell(`${LEFT_LETTER_START}22`);
    docTitle.value = DOC_TITLE;
    docTitle.alignment = LEFT_FORMAT;
    worksheet.mergeCells(`${LEFT_LETTER_START}22:${LEFT_LETTER_END}25`);
    docTitle.font = TITLE_FONT;

    //Document subtitle
    let docSubTitle = worksheet.getCell(`${LEFT_LETTER_START}26`);
    docSubTitle.value = `№ ${insurenceAct.number} від ${localMoment.format('DD.MM.YYYY')}`;
    docSubTitle.alignment = {...LEFT_FORMAT, vertical: 'top'};
    worksheet.mergeCells(`${LEFT_LETTER_START}26:${RIGHT_LETTER_END}27`);
    docSubTitle.font = TITLE_FONT;
    docSubTitle.border = BORDER_BOTTOM_MEDIUM;

    //Document preambule
    let docPreambule = worksheet.getCell(`${LEFT_LETTER_START}28`);
    docPreambule.value = getPreamble(clinic, insurer);
    docPreambule.alignment = {...LEFT_FORMAT, vertical: 'top',};
    worksheet.mergeCells(`${LEFT_LETTER_START}28:${RIGHT_LETTER_END}${ROW_NUM_START - 1}`);
    docPreambule.font = SUBTITLE_FONT;

    /**
     * Document footer
     */
        //First available row after table
    let nextAvailableRow = (ROW_NUM_START + table.height + 1);

    //Totals
    // Total without tax
    let docTotalsText = worksheet.getCell(`${RIGHT_LETTER_START}${nextAvailableRow}`);
    docTotalsText.value = __('Разом:');
    docTotalsText.alignment = ALIGN_TOP_RIGHT;
    worksheet.mergeCells(`${RIGHT_LETTER_START}${nextAvailableRow}:${LETTER_H}${nextAvailableRow + 1}`);

    let amount = getTotal(data);

    let docTotalsNumbers = worksheet.getCell(`${LETTER_J}${nextAvailableRow}`);
    docTotalsNumbers.value = getFormatedAmount(amount, clinic.not_round_cost);
    docTotalsNumbers.alignment = ALIGN_TOP_RIGHT;
    worksheet.mergeCells(`${LETTER_J}${nextAvailableRow}:${RIGHT_LETTER_END}${nextAvailableRow + 1}`);

    nextAvailableRow = nextAvailableRow + 2;
    // Total tax
    let docTotalsTax = worksheet.getCell(`${RIGHT_LETTER_START}${nextAvailableRow}`);
    docTotalsTax.value = __('Сума ПДВ:');
    docTotalsTax.alignment = ALIGN_TOP_RIGHT;
    worksheet.mergeCells(`${RIGHT_LETTER_START}${nextAvailableRow}:${LETTER_H}${nextAvailableRow + 1}`);

    let docTotalsTaxNumbers = worksheet.getCell(`${LETTER_J}${nextAvailableRow}`);
    docTotalsTaxNumbers.value = 0;
    docTotalsTaxNumbers.alignment = ALIGN_TOP_RIGHT;
    worksheet.mergeCells(`${LETTER_J}${nextAvailableRow}:${RIGHT_LETTER_END}${nextAvailableRow + 1}`);

    nextAvailableRow = nextAvailableRow + 2;

    // Total with tax
    let docTotalsWithTax = worksheet.getCell(`${RIGHT_LETTER_START}${nextAvailableRow}`);
    docTotalsWithTax.value = __('Всього із ПДВ:');
    docTotalsWithTax.alignment = ALIGN_TOP_RIGHT;
    worksheet.mergeCells(`${RIGHT_LETTER_START}${nextAvailableRow}:${LETTER_H}${nextAvailableRow + 1}`);

    let docTotalsWithTaxNumbers = worksheet.getCell(`${LETTER_J}${nextAvailableRow}`);
    docTotalsWithTaxNumbers.value = getFormatedAmount(amount, clinic.not_round_cost);
    docTotalsWithTaxNumbers.alignment = ALIGN_TOP_RIGHT;
    worksheet.mergeCells(`${LETTER_J}${nextAvailableRow}:${RIGHT_LETTER_END}${nextAvailableRow + 1}`);

    nextAvailableRow = nextAvailableRow + 2;

    //Amount to string
    let stringTotalsNumbers = worksheet.getCell(`${LEFT_LETTER_START}${nextAvailableRow}`);
    stringTotalsNumbers.value = getDocTotalsSummary(Math.round(amount));
    stringTotalsNumbers.alignment = {vertical: 'top', wrapText: true};
    stringTotalsNumbers.border = BORDER_BOTTOM_MEDIUM;
    worksheet.mergeCells(`${LEFT_LETTER_START}${nextAvailableRow}:${RIGHT_LETTER_END}${nextAvailableRow + 4}`);

    nextAvailableRow = nextAvailableRow + 5;

    //Requisites header
    let requisiteTitleLeft = worksheet.getCell(`${LEFT_LETTER_START}${nextAvailableRow}`);
    let requisiteTitleRight = worksheet.getCell(`${RIGHT_LETTER_START}${nextAvailableRow}`);
    requisiteTitleLeft.value = FROM_CLINIC;
    requisiteTitleRight.value = FROM_INSURER;
    requisiteTitleLeft.alignment = {...LEFT_FORMAT, vertical: 'middle'};
    requisiteTitleRight.alignment = {...COMMON_FORMAT, vertical: 'middle'};
    worksheet.mergeCells(`${LEFT_LETTER_START}${nextAvailableRow}:${LEFT_LETTER_END}${nextAvailableRow + 3}`);
    worksheet.mergeCells(`${RIGHT_LETTER_START}${nextAvailableRow}:${RIGHT_LETTER_END}${nextAvailableRow + 3}`);
    requisiteTitleLeft.font = {...SUBTITLE_FONT, bold: true};
    requisiteTitleRight.font = {...SUBTITLE_FONT, bold: true};
    requisiteTitleLeft.border = BORDER_BOTTOM_MEDIUM;
    requisiteTitleRight.border = BORDER_BOTTOM_MEDIUM;

    nextAvailableRow = nextAvailableRow + 4;

    //Requisites position
    let requisiteLeftSigner = worksheet.getCell(`${LEFT_LETTER_START}${nextAvailableRow}`);
    let requisiteRightSigner = worksheet.getCell(`${RIGHT_LETTER_START}${nextAvailableRow}`);
    requisiteLeftSigner.value = getModelField(clinic, 'signer_position') + '\n' + getModelField(clinic, 'signer');
    requisiteRightSigner.value = getModelField(insurer, 'signer_position') + '\n' + getModelField(insurer, 'signer');
    requisiteLeftSigner.alignment = LEFT_FORMAT;
    requisiteRightSigner.alignment = COMMON_FORMAT;
    worksheet.mergeCells(`${LEFT_LETTER_START}${nextAvailableRow}:${LETTER_C}${nextAvailableRow + 3}`);
    worksheet.mergeCells(`${RIGHT_LETTER_START}${nextAvailableRow}:${LETTER_I}${nextAvailableRow + 3}`);
    requisiteLeftSigner.font = {...REGULAR_FONT, bold: true};
    requisiteRightSigner.font = {...REGULAR_FONT, bold: true};

    nextAvailableRow = nextAvailableRow + 4;

    //Position note
    let positionLeftNote = worksheet.getCell(`${LEFT_LETTER_START}${nextAvailableRow}`);
    positionLeftNote.value = SIGNER_NOTE;
    positionLeftNote.alignment = {...LEFT_FORMAT, wrapText: true};
    worksheet.mergeCells(`${LEFT_LETTER_START}${nextAvailableRow}:${LEFT_LETTER_END}${nextAvailableRow + 2}`);
    positionLeftNote.font = REGULAR_FONT;

    nextAvailableRow = nextAvailableRow + 3;

    //Current Date
    let requisiteLeftDate = worksheet.getCell(`${LEFT_LETTER_START}${nextAvailableRow}`);
    let requisiteRightDate = worksheet.getCell(`${RIGHT_LETTER_START}${nextAvailableRow}`);
    requisiteLeftDate.value = dateFormat(localMoment, 'DD.MM.YYYY');
    requisiteRightDate.value = dateFormat(localMoment, 'DD.MM.YYYY');
    requisiteLeftDate.alignment = LEFT_FORMAT;
    requisiteRightDate.alignment = COMMON_FORMAT;
    worksheet.mergeCells(`${LEFT_LETTER_START}${nextAvailableRow}:${LETTER_C}${nextAvailableRow + 1}`);
    worksheet.mergeCells(`${RIGHT_LETTER_START}${nextAvailableRow}:${LETTER_I}${nextAvailableRow + 1}`);
    requisiteLeftDate.font = {...REGULAR_FONT, bold: true};
    requisiteRightDate.font = {...REGULAR_FONT, bold: true};

    nextAvailableRow = nextAvailableRow + 2;

    //Companies requisites
    let requisiteLeft = worksheet.getCell(`${LEFT_LETTER_START}${nextAvailableRow}`);
    let requisiteRight = worksheet.getCell(`${RIGHT_LETTER_START}${nextAvailableRow}`);
    requisiteLeft.value = getModelFields(clinic, ['official_name', EGRPO, 'contact_phone', 'official_additional', 'official_address']);
    requisiteRight.value = getModelFields(insurer, ['name', EGRPO, 'phone_number', 'tax_number', 'location']);
    requisiteLeft.alignment = {...LEFT_FORMAT, wrapText: true, vertical: 'top'};
    requisiteRight.alignment = {...COMMON_FORMAT, wrapText: true, vertical: 'top'};
    worksheet.mergeCells(`${LEFT_LETTER_START}${nextAvailableRow}:${LEFT_LETTER_END}${nextAvailableRow + 7}`);
    worksheet.mergeCells(`${RIGHT_LETTER_START}${nextAvailableRow}:${RIGHT_LETTER_END}${nextAvailableRow + 7}`);
    requisiteLeft.font = REGULAR_FONT;
    requisiteRight.font = REGULAR_FONT;

    return Promise.resolve({book: workbook, amount});
}
