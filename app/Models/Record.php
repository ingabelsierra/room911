<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
     
     protected $fillable = [
        
        'employee_id','card','identification_number','action', 'created_at', 'updated_at'
    ];   
    
    public function employee()
    {
        return $this->belongsTo('App\Models\Employee');
    }
  
}
