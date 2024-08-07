<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $table = 'uploads';

    protected $fillable = [
        'model_type',
        'model_id',
        'name',
        'path',
        'mime_type',
    ];

    public $appends = ['url', 'preview', 'size'];

    public function getUrlAttribute()
    {
        return  asset("storage/" . $this->path);
    }

    public function getPreviewAttribute()
    {
        if (strpos($this->mime_type, 'image/') === 0) {
            return $this->url;
        } else {
            $type = '';

            switch ($this->mime_type) {
                    // Code-related MIME types
                case "text/javascript":
                case "application/javascript":
                case "application/x-javascript":
                case "text/css": // CSS files
                case "application/xml": // XML
                case "text/html": // HTML files
                    $type = "code";
                    break;

                    // Document MIME types
                case "application/msword": // DOC
                case "application/vnd.openxmlformats-officedocument.wordprocessingml.document": // DOCX
                case "application/pdf":
                    $type = "doc";
                    break;

                    // Video MIME types
                case "video/mp4":
                case "video/x-msvideo": // AVI
                case "video/quicktime": // MOV
                    $type = "video";
                    break;

                    // Music/audio MIME types
                case "audio/mpeg": // MP3
                case "audio/mp3":
                case "audio/wav":
                case "audio/ogg":
                    $type = "music";
                    break;

                    // PDF MIME type
                case "application/pdf":
                    $type = "pdf";
                    break;

                    // Presentation MIME types
                case "application/vnd.ms-powerpoint": // PPT
                case "application/vnd.openxmlformats-officedocument.presentationml.presentation": // PPTX
                    $type = "ppt";
                    break;

                    // Spreadsheet MIME types
                case "application/vnd.ms-excel": // XLS
                case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet": // XLSX
                    $type = "sheet";
                    break;

                    // Text MIME types
                case "text/plain":
                case "text/csv":
                    $type = "text";
                    break;

                    // Compressed files MIME types
                case "application/zip":
                case "application/x-zip-compressed":
                case "application/x-rar-compressed":
                case "application/x-7z-compressed":
                    $type = "zip";
                    break;

                    // Default document type for uncategorized or less common MIME types
                default:
                    $type = "document";
                    break;
            }

            return "/assets/svgs/$type.svg"; // Adjust the placeholder path as per your file structure
        }
    }

    public function model(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function getSizeAttribute()
    {
        $filePath = public_path("storage/" . $this->path);
        if (file_exists($filePath)) {
            $fileSize = filesize($filePath);

            // Convert to MB if size is greater than 1000 KB
            if ($fileSize > 1000 * 1024) {
                return round($fileSize / (1024 * 1024), 2) . " MB";
            }

            // Convert to KB if size is greater than 1 KB
            if ($fileSize > 1024) {
                return round($fileSize / 1024, 2) . " KB";
            }

            return $fileSize . " bytes";
        }
        return null;
    }
}
