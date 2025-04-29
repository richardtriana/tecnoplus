<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdjustmentNoteReason extends Model
{
    use HasFactory;

    protected $table = 'adjustment_note_reasons';

    protected $fillable = [
        'code',
        'description'
    ];
}
