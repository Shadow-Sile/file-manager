<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'original_name',
        'path',
        'mime_type',
        'size',
        'extension',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        } else {
            return $bytes.' bytes';
        }
    }

    public function getIconAttribute(): string
    {
        $icons = [
            'image' => '🖼️',
            'video' => '🎬',
            'audio' => '🎵',
            'pdf' => '📄',
            'text' => '📝',
            'zip' => '📦',
        ];

        $type = explode('/', $this->mime_type)[0];
        $ext = strtolower($this->extension ?? '');

        if ($type === 'image') {
            return $icons['image'];
        }
        if ($type === 'video') {
            return $icons['video'];
        }
        if ($type === 'audio') {
            return $icons['audio'];
        }
        if ($ext === 'pdf') {
            return $icons['pdf'];
        }
        if ($type === 'text') {
            return $icons['text'];
        }
        if (in_array($ext, ['zip', 'rar', 'tar', 'gz', '7z'])) {
            return $icons['zip'];
        }

        return '📎';
    }
}
