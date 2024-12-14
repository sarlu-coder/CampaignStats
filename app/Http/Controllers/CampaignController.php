<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignStats;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Display list of campaigns and aggregate revenue for each campaign
     */
    public function index()
    {
        return view('campaign_table');
        // @TODO implement
    }

    public function getCampaigns(Request $request){
      
        // Page Length
        $pageNumber = ( $request->start / $request->length )+1;
        $pageLength = $request->length;
        $skip       = ($pageNumber-1) * $pageLength;

        // Page Order
        $orderColumnIndex = $request->order[0]['column'] ?? '0';
        $orderBy = $request->order[0]['dir'] ?? 'desc';

        $query = \DB::table('campaigns')->join('campaign_stats', 'campaigns.id', '=', 'campaign_stats.utm_campaign')->select('campaigns.id','campaigns.utm_campaign',DB::raw('ROUND(SUM(revenue),5) as total_revenue'),DB::raw('COUNT(*) OVER () AS TotalRecords'))->groupBy('campaigns.id','campaigns.utm_campaign');

        // Search
        $search = $request->search;
        if(!empty(trim($search))){
            $query = $query->where(function($query) use ($search){
                $query->orWhere('campaigns.utm_campaign', 'like', "%".$search."%");
            });
        }

        $orderByName = 'campaigns.utm_campaign';
        
        $total_query = \DB::table('campaigns')->join('campaign_stats', 'campaigns.id', '=', 'campaign_stats.utm_campaign')->select(DB::raw('COUNT(*) OVER () AS TotalRecords'))->groupBy('campaigns.id','campaigns.utm_campaign');
        if(!empty(trim($search))){
            $total_query->where('campaigns.utm_campaign', 'like', "%".$search."%");
        }
        $recordsTotal = $total_query->get()[0]->TotalRecords;
        
        $query = $query->orderBy($orderByName, $orderBy);
        
        $recordsFiltered = $recordsTotal;
        $users = $query->skip($skip)->take($pageLength)->get();

        return response()->json(["draw"=> $request->draw, "recordsTotal"=> $recordsTotal, "recordsFiltered" => $recordsFiltered, 'data' => $users], 200);
    }

    /**
     * Display a specific campaign with a hourly breakdown of all revenue
     */
    public function show(Campaign $campaign)
    {
        return view('campaign_hourly_revenue',['utm_campaign_id' => $campaign->id,'utm_campaign' => $campaign->utm_campaign]);
    }

    public function getHourlyRevenue(Request $request){
      
        // Page Length
        $pageNumber = ( $request->start / $request->length )+1;
        $pageLength = $request->length;
        $skip       = ($pageNumber-1) * $pageLength;

        // Page Order
        $orderColumnIndex = $request->order[0]['column'] ?? '0';
        $orderBy = $request->order[0]['dir'] ?? 'desc';

        $query = \DB::table('campaign_stats')
        ->select('monetization_timestamp',DB::raw('ROUND(SUM(revenue),5) as hourly_revenue'),DB::raw('COUNT(*) OVER () AS TotalRecords'),DB::raw('COUNT(revenue) AS RevenueCount'),DB::raw('CONCAT(DATE_FORMAT(monetization_timestamp,"%Y-%m-%d %H:00")," - ",DATE_ADD(DATE_FORMAT(monetization_timestamp,"%Y-%m-%d %H:00"), INTERVAL 1 HOUR)) as datetime_group'))
        ->where('utm_campaign', '=', $request->utm_campaign)
        ->groupBy(DB::raw('hour( `monetization_timestamp` ), day( `monetization_timestamp` )'));

        $orderByName = 'monetization_timestamp';

        $total_query = \DB::table('campaign_stats')
        ->select(DB::raw('ROUND(SUM(revenue),5) as hourly_revenue'),DB::raw('COUNT(*) OVER () AS TotalRecords'))
        ->where('utm_campaign', '=', $request->utm_campaign)
        ->groupBy(DB::raw('hour( `monetization_timestamp` ), day( `monetization_timestamp` )'));
        
        $recordsTotal = $total_query->get()[0]->TotalRecords;
        $query = $query->orderBy($orderByName, $orderBy);
        
        $recordsFiltered = $recordsTotal;
        $users = $query->skip($skip)->take($pageLength)->get();

        return response()->json(["draw"=> $request->draw, "recordsTotal"=> $recordsTotal, "recordsFiltered" => $recordsFiltered, 'data' => $users], 200);
    }

    /**
     * Display a specific campaign with the aggregate revenue by utm_term
     */
    public function publishers(Campaign $campaign)
    {
        return view('campaign_term_revenue',['utm_campaign_id' => $campaign->id,'utm_campaign' => $campaign->utm_campaign]);

    }

    public function getTermRevenue(Request $request){
      
        // Page Length
        $pageNumber = ( $request->start / $request->length )+1;
        $pageLength = $request->length;
        $skip       = ($pageNumber-1) * $pageLength;

        // Page Order
        $orderColumnIndex = $request->order[0]['column'] ?? '0';
        $orderBy = $request->order[0]['dir'] ?? 'desc';

        $query = \DB::table('campaign_stats')
        ->select('utm_term',DB::raw('ROUND(SUM(revenue),5) as term_revenue'),DB::raw('COUNT(revenue) AS revenue_count'))
        ->where('utm_campaign', '=', $request->utm_campaign)
        ->groupBy('utm_term');

        $search = $request->search;
        if(!empty(trim($search))){
            $query = $query->where(function($query) use ($search){
                $query->orWhere('utm_term', 'like', "%".$search."%");
            });
        }

        $orderByName = 'utm_term';

        $total_query = \DB::table('campaign_stats')
        ->select(DB::raw('ROUND(SUM(revenue),5) as term_revenue'),DB::raw('COUNT(*) OVER () AS TotalRecords'))
        ->where('utm_campaign', '=', $request->utm_campaign)
        ->groupBy('utm_term');
        
        $recordsTotal = $total_query->get()[0]->TotalRecords;
        $query = $query->orderBy($orderByName, $orderBy);
        
        $recordsFiltered = $recordsTotal;
        $users = $query->skip($skip)->take($pageLength)->get();

        return response()->json(["draw"=> $request->draw, "recordsTotal"=> $recordsTotal, "recordsFiltered" => $recordsFiltered, 'data' => $users], 200);
    }
}