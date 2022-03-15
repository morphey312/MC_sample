<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileAttachment extends BaseModel implements SharedResourceInterface
{
    use SharedResource, HasConstraint;
    
    const STORAGE_DIR = 'uploads';
    
    /**
     * @var string
     */ 
    protected $table = 'file_attachments';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'mime_type',
        'size',
        'md5_sum',
        'disk',
        'path', 
    ];
    
    /**
     * @var array
     */
    protected $deleting_constraints = [
        'references',
    ];
    
    /**
     * @inherit
     */
    protected static function boot()
    {
        parent::boot();
        
        static::deleted(function ($model) {
            Storage::disk($model->disk)->delete($model->path);
        });
    }

    /**
     * Related references
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */ 
    public function references()
    {
        return $this->hasMany(FileAttachment\Reference::class, 'attachment_id');
    }
    
    /**
     * Make new file attachment from uploaded file
     * 
     * @param UploadedFile $file
     * @param bool $unique
     * @param string $disk
     * 
     * @return FileAttachment
     */ 
    public static function createFromUpload(UploadedFile $file, $unique = true, $disk = null)
    {
        $md5sum = md5_file($file->getPathname());
        $name = $file->getClientOriginalName();
        
        if ($unique) {
            $existing = static::where('md5_sum', $md5sum)
                ->where('name', $name)
                ->first();
            if ($existing !== null) {
                return $existing;
            }
        }
        
        if ($disk === null) {
            $disk = config('filesystems.default');
        }
        
        $attachment = new static();
        $attachment->name = $name;
        $attachment->mime_type = $file->getClientMimeType();
        $attachment->size = $file->getClientSize();
        $attachment->md5_sum = $md5sum;
        $attachment->disk = $disk;
        $attachment->path = $file->store(self::STORAGE_DIR . '/' . date('Y/m/d'), $disk);
        $attachment->save();
        
        return $attachment;
    }
    
    /**
     * Get download url
     * 
     * @return string
     */ 
    public function getDownloadUrl()
    {
        return route('downloadFile', ['id' => $this->id]);
    }
}
