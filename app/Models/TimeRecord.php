<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'start_at',
        'end_at',
    ];

    /**
     * Get the user that owns the TimeRecord
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return date('d/m/Y', strtotime($value));
    }

    public function getStartAtAttribute($value)
    {
        return date('H:i', strtotime($value));
    }

    public function getEndAtAttribute($value)
    {
        return $value ? date('H:i', strtotime($value)) : 'Aguardando Saída';
    }
}
