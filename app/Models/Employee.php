<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    
    protected $fillable = [
        
        'name','last_name','card','identification_number', 'is_active','deparment', 'created_at', 'updated_at'
    ];    
  
    public function records()
    {
        return $this->hasMany('App\Models\Record');
    }  
}
