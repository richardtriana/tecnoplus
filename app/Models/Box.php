<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prefix'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
    protected $appends = [
        'process'
    ];

    public function getProcessAttribute(){
        
        $orders = Order::where('bill_number','LIKE','%'.$this->prefix.'%')->count();

        return $orders > 0 ? true: false;
    }

    public function consecutiveBox(){
        return $this->hasMany(ConsecutiveBox::class);
    }
}
