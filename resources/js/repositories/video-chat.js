import BaseRepository from '@/repositories/base-repository';
import VideoChat from "@/models/video-chat";

class VideoChatRepository extends BaseRepository
{
    /**
     * Constructor
     */
    constructor(options = {}) {
        super(options);
        this.endpoint = '/api/v1/video-chat';
    }

    /**
     * @inheritdoc
     */
    transformRow(row) {
        return new VideoChat(row);
    }
}

export default VideoChatRepository;
