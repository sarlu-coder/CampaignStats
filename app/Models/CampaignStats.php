<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignStats extends Model
{
    use HasFactory;
    protected $fillable = ['utm_campaign','utm_term','monetization_timestamp','revenue'];
}
