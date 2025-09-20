<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectFile extends Model
{
 use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id',
        'file_path',
        'original_name',
        'content',
        'updated_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

// العلاقات
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // ✅ علاقة جديدة مع المستخدم اللي عدل الملف
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors & Mutators
    public function getFileSizeAttribute()
    {
        return strlen($this->content ?? '');
    }

    public function getFileExtensionAttribute()
    {
        return pathinfo($this->original_name, PATHINFO_EXTENSION);
    }

    // ✅ Helper method لتحديد إذا كان الملف تم تعديله من قبل مستخدم معين
    public function wasUpdatedBy($userId)
    {
        return $this->updated_by == $userId;
    }    

}
