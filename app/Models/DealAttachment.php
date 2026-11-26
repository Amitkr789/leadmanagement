<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DealAttachment extends Model
{
    protected $fillable = ['deal_id', 'file_name', 'file_path', 'mime_type', 'file_size', 'uploaded_by'];

    protected $appends = ['file_url', 'file_size_label'];

    public function deal(): BelongsTo     { return $this->belongsTo(Deal::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(Admin::class, 'uploaded_by'); }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeLabelAttribute(): string
    {
        $bytes = (float) $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }
}