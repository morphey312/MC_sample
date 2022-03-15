import Result from '@/models/analysis/result';

export default {
    methods: {
        createResultModel(data, filters) {
            let result = new Result();
            result.castAnalysisDataToEntity(data, filters);
            return result;
        },
    }
}