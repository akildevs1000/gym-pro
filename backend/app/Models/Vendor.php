<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['full_name', 'full_address', 'name_with_number'];

    // public function expense()
    // {
    //     return $this->hasMany(Expense::class);
    // }


    public function getFullNameAttribute()
    {
        $name = trim("{$this->title} {$this->first_name} {$this->last_name}");
        return $name !== '' ? $name : '---';
    }

    public function getNameWithNumberAttribute()
    {
        return $this->title . ' ' . $this->first_name . ' ' . $this->last_name . " - " . $this->phone_number;
    }

    public function getFullAddressAttribute()
    {
        $full_addres = "";

        if ($this->state) {
            $full_addres .= $this->state;
        }

        if ($this->country) {
            $full_addres .= ", "  . $this->country;
        }

        if ($this->zip_code) {
            $full_addres .= ", "  . $this->zip_code;
        }
        return  $full_addres;
    }
}
