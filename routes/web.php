<?php

use App\Http\Controllers\CampaignController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CampaignController::class, 'index'])->name('home');
Route::post('/getCampaigns', [CampaignController::class, 'getCampaigns'])->name('campaign.getCampaigns');

Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaign');
Route::post('/getHourlyRevenue', [CampaignController::class, 'getHourlyRevenue'])->name('campaign.hourlyStats');

Route::get('/campaigns/{campaign}/publishers', [CampaignController::class, 'publishers'])->name('publishers');
Route::post('/getTermRevenue', [CampaignController::class, 'getTermRevenue'])->name('campaign.termStats');