<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = ['code', 'name', 'name_ar', 'contact_person', 'phone', 'mobile', 'email', 'address', 'city', 'country', 'vat_no', 'balance', 'payment_terms_days', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

}
