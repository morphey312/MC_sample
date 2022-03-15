import translationServer from '@/services/translation';
import ua from './ua';
import outpatientCardUa from './outpatient-card-ua';
import sk from './sk';
import outpatientCardSk from './outpatient-card-sk';
import moment from 'moment';
import elementUIlocale from 'element-ui/lib/locale'
import elementUIru from 'element-ui/lib/locale/lang/ru-RU';
import elementUIua from 'element-ui/lib/locale/lang/ua';
import elementUIsk from 'element-ui/lib/locale/lang/sk';

translationServer.register('ua', ua);
translationServer.register('ua', outpatientCardUa, 'outpatient-card');

translationServer.register('sk', sk);
translationServer.register('sk', outpatientCardSk, 'outpatient-card');

window.__ = (string, args = {}) => {
    return translationServer.translate(string, args);
};

const localizeMoment = (lang) => {
    moment.locale(lang, {
        months : [__('Январь'), __('Февраль'), __('Март'), __('Апрель'), __('Май'), __('Июнь'), __('Июль'), __('Август'), __('Сентябрь'), __('Октябрь'), __('Ноябрь'), __('Декабрь')], 
        monthsShort : [__('Янв'), __('Фев'), __('Мар'), __('Апр'), __('Май'), __('Июн'), __('Июл'), __('Авг'), __('Сен'), __('Окт'), __('Ноя'), __('Дек')],
        weekdays : [__('Воскресенье'), __('Понедельник'), __('Вторник'), __('Среда'), __('Четверг'), __('Пятница'), __('Суббота')],
        weekdaysMin : [__('Вc'), __('Пн'), __('Вт'), __('Ср'), __('Чт'), __('Пт'), __('Сб')],
    });
}

const localizeElementUI = (lang) => {
    switch (lang) {
        case 'ua':
            elementUIlocale.use(elementUIua);
            break;
        case 'sk':
            elementUIlocale.use(elementUIsk);
            break;
        default: 
            elementUIlocale.use(elementUIru);
            break;
    }
}

const localizeComponents = (lang) => {
    localizeMoment(lang);
    localizeElementUI(lang);
}

localizeComponents(translationServer.lang);

export default localizeComponents;