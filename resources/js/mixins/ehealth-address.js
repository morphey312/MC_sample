export default {
    methods: {
        formatRegionName(region) {
            if (region.substr(0, 2) === 'М.') {
                return __('м. {region}', {region: this.normalizeCase(region.substr(2))});
            }
            return __('{region} обл.', {region: this.normalizeCase(region)});
        },
        formatDistrictName(district) {
            return __('{district} р-н', {district: this.normalizeCase(district)});
        },
        formatSettlementName(name, type) {
            name = this.normalizeCase(name);
            switch (type) {
                case 'CITY': return __('м. {name}', {name});
                case 'SETTLEMENT': return __('сел. {name}', {name});
                case 'TOWNSHIP': return __('с.м.т. {name}', {name});
                case 'VILLAGE': return __('с. {name}', {name});
            }
            return name;
        },
        formatStreetName(name, type) {
            switch (type) {
                case 'ALLEY': return __('алея {name}', {name});
                case 'ASCENT': return __('узвіз {name}', {name});
                case 'AVENUE': return __('просп. {name}', {name});
                case 'BLIND_STREET': return __('тупик {name}', {name});
                case 'BLOCK': return __('квартал {name}', {name});
                case 'BOARGING_HOUSE': return __('пансіонат {name}', {name});
                case 'BOULEVARD': return __('бул. {name}', {name});
                case 'COUNTRY_LANE': return __('завулок {name}', {name});
                case 'CROSSROADS': return __('розвилка {name}', {name});
                case 'DACHA_COOPERATIVE': return __('дачний кооператив {name}', {name});
                case 'ENTRANCE': return __('в\'їзд {name}', {name});
                case 'FORESTRY': return __('лісництво {name}', {name});
                case 'GARDEN_SQUARE': return __('сквер {name}', {name});
                case 'GROVE': return __('гай {name}', {name});
                case 'HAMLET': return __('хутір {name}', {name});
                case 'HIGHWAY': return __('ш. {name}', {name});
                case 'HOUSING_AREA': return __('житловий масив {name}', {name});
                case 'ISLAND': return __('о. {name}', {name});
                case 'LANE': return __('пров. {name}', {name});
                case 'LINE': return __('лінія {name}', {name});
                case 'MAIDAN': return __('майдан {name}', {name});
                case 'MASSIF': return __('масив {name}', {name});
                case 'MICRODISTRICT': return __('мкрн. {name}', {name});
                case 'MILITARY_BASE': return __('військова частина {name}', {name});
                case 'MINE': return __('шахта {name}', {name});
                case 'PARK': return __('парк {name}', {name});
                case 'PASS': return __('прохід {name}', {name});
                case 'PASSAGE': return __('проїзд {name}', {name});
                case 'PASSING_LOOP': return __('роз\'їзд {name}', {name});
                case 'RIVER_SIDE': return __('наб. {name}', {name});
                case 'ROAD': return __('дорога {name}', {name});
                case 'ROADWAY': return __('тракт {name}', {name});
                case 'SANATORIUM': return __('санаторій {name}', {name});
                case 'SELECTION_BASE': return __('селекційна станція {name}', {name});
                case 'SETTLEMENT': return __('сел. {name}', {name});
                case 'SMALL VILLAGE': return __('присілок {name}', {name});
                case 'SQUARE': return __('площа {name}', {name});
                case 'STATE_FARM': return __('радгосп {name}', {name});
                case 'STATION': return __('ст. {name}', {name});
                case 'STATION_SETTLEMENT': return __('будинок МПС {name}', {name});
                case 'STREET': return __('вул. {name}', {name});
                case 'TRACK': return __('траса {name}', {name});
                case 'TRACT': return __('урочище {name}', {name});
                case 'WAY': return __('шлях {name}', {name});
            }
            return name;
        },
        formatBuildingNumber(number) {
            return __('буд. {number}', {number});
        },
        formatApartmentNumber(number) {
            return __('кв. {number}', {number});
        },
        normalizeCase(v) {
            return v.split(/([^а-яА-ЯҐґЄєІіЇїa-zA-Z]+)/)
                .map((w, i) => (i % 2 === 1) ? w : _.capitalize(w))
                .join('');
        },
    }
}
