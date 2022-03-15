/** @scan-translations-off */

class MoneyFormatter
{
    numLetters(k, d) {  // целое число прописью, это основа
        let i = '';
        let e = [
            ['','тисяч','мільйон','мільярд','трильйон'],
            ['а','і',''],
            ['','а','ів']
        ];

        if (k == '' || k == '0') {
            return ' нуль'; // 0
        }

        k = k.split(/(?=(?:\d{3})+$)/);  // разбить число в массив с трёхзначными числами

        if (k[0].length == 1) {
            k[0] = '00'+k[0];
        }

        if (k[0].length == 2) {
            k[0] = '0'+k[0];
        }

        let convertThousand = (k, d) => {  // преобразовать трёхзначные числа
            let e = [
                ['',' один',' два',' три',' чотири',' п\'ять',' шість',' сім',' вісім',' дев\'ять'],
                [' десять',' одинадцять',' дванадцять',' тринадцять',' чотирнадцять',' п\'ятнадцять',' шістнадцять',' сімнадцять',' вісімнадцять',' дев\'ятнадцять'],
                ['','',' двадцять',' тридцять',' сорок',' п\'ятдесят',' шістьдесят',' сімдесят',' вісімдесят',' д\'евяносто'],
                ['',' сто',' двісти',' триста',' чотириста',' п\'ятсот',' шістсот',' сімсот',' вісімсот',' дев\'ятсот'],
                ['',' одна',' дві']
            ];
            return e[3][k[0]] + (k[1] == 1 ? e[1][k[2]] : e[2][k[1]] + (d ? e[4][k[2]] : e[0][k[2]]));
        }

        for (let j = (k.length - 1); j >= 0; j--) {  // соединить трёхзначные числа в одно число, добавив названия разрядов с окончаниями
          if (k[j] != '000') {
            i = (((d && j == (k.length - 1)) || j == (k.length - 2)) && (k[j][2] == '1' || k[j][2] == '2') ? convertThousand(k[j],1) : convertThousand(k[j])) + this.declOfNum(k[j], e[0][k.length - 1 - j], (j == (k.length - 2) ? e[1] : e[2])) + i;
          }
        }

        return i;
    }

    declOfNum(n, t, o) {  // склонение именительных рядом с числительным: число (typeof = string), корень (не пустой), окончание
        let k = [2,0,1,1,1,2,2,2,2,2];
        return (t == '' ? '' : ' ' + t + (n[n.length-2] == "1" ? o[2] : o[k[n[n.length-1]]]));
    }

    razUp(e) {  // сделать первую букву заглавной и убрать лишний первый пробел
        return e[1].toUpperCase() + e.substring(2);
    }

    moneyToString(a) {
        a = Number(a).toFixed(2).split('.');  // округлить до сотых и сделать массив двух чисел: до точки и после неё
        return this.razUp(this.numLetters(a[0]) + this.declOfNum(a[0], 'грив', ['ня','ні','ень']) + ' ' + a[1] + this.declOfNum(a[1], 'копі', ['йка','йки','йок']));
    }
}

export default new MoneyFormatter();
