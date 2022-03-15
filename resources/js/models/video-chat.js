import BaseModel from '@/models/base-model';

class VideoChat extends BaseModel
{
    /**
     * @inheritdoc
     */
    getModelClass() {
        return 'VideoChat';
    }

    /**
     * @inheritdoc
     */
    defaults() {
        return {
            appointment_id: null,
            closed: false,
            initiated: false,
            room_name: '',
            room_sid: null,
            composition_sid: null,
            download_link: null,
            composition_status: null,
            room_status: null,
            composition_progress: null,
            duration: null,
            composition_media_url: null,
        }
    }

    /**
     * @inheritdoc
     */
    routes() {
        return {
            create: '/api/v1/video-chat',
            fetch: '/api/v1/video-chat/{id}',
            requestComposition: '/api/v1/video-chat/getVideoComposition/{id}',
            requestParticipants: '/api/v1/video-chat/requestParticipantsLogs/{id}',
        }
    }

    /**
     * Get video chat duration in minutes
     *
     * @returns {number}
     */
    get duration_in_minutes() {
        return Math.round(this.duration / 60);
    }


    /**
     * Get video cost
     *
     * @returns {string|null}
     */
    get twilio_price() {
        if(typeof this.duration_in_minutes === 'number'){
            return (this.duration_in_minutes * 0.016).toFixed(2);
        }

        return null;
    }

    /**
     * Request Video Composition
     *
     * @returns {Promise}
     */
    requestComposition() {
        let route  = this.getRoute('requestComposition');
        let params = this.getRouteParameters();
        let method = 'GET';
        let url    = this.getURL(route, params);
        let data   = {};

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response);
        });
    }

    /**
     * Request Video Composition
     *
     * @returns {Promise}
     */
    requestParticipantsLogs() {
        let route  = this.getRoute('requestParticipants');
        let params = this.getRouteParameters();
        let method = 'GET';
        let url    = this.getURL(route, params);
        let data   = {};

        return this.getRequest({method, url, data}).send().then((response) => {
            return Promise.resolve(response);
        });
    }

}

export default VideoChat;
