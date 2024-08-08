<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Region;
use App\Models\SubTenderActivity;
use App\Models\Tender;
use App\Models\TenderActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SiteMapController extends Controller
{
    private array $non_paginated_sitemaps = ['cities', 'regions', 'tender-activities', 'sub-tender-activities'];

    public function index($locale = 'ar')
    {
        $sitemaps = [];
        $url = env('FRONT_APP_URL');

        foreach ($this->non_paginated_sitemaps as $sitemap) {
            $sitemaps[] = "$url/sitemap/$locale/$sitemap";
        }
        $tenders_count = Model::count();
        $paginated_sitemaps = ceil($tenders_count / 5000);

        for ($i = 1; $i <= $paginated_sitemaps; $i++) {
            $this->non_paginated_sitemaps[] = $tender = "latest-tenders-$i";
            $sitemaps[] = "$url/sitemap/$locale/$tender";
        }

        return $this->renderXML('sitemaps.index', compact('sitemaps'));
    }

    public function sitemap($locale = 'ar', $sitemap)
    {
        if (!in_array($sitemap, $this->non_paginated_sitemaps) && !str_contains($sitemap, 'latest-tenders-'))
            return abort(404);

        $url = env('FRONT_APP_URL');

        switch ($sitemap) {
            case 'cities':
                $urls = DB::select("select concat('$url/$locale/cities/', slug) as url from cities;");
                break;
            case 'regions':
                $urls = DB::select("select concat('$url/$locale/regions/', slug) as url from regions;");
                break;
            case 'tender-activities':
                $urls = DB::select("select concat('$url/$locale/tender-activities/', slug) as url from tender_activities;");
                break;
            case 'sub-tender-activities':
                $sitemaps = [];
                $sub_tender_activities = SubTenderActivity::get()->map(function ($sub_tender_activity) use ($url, $locale, &$sitemaps) {
                    $sitemaps[] = "$url/sitemap/$locale/sub-tender-activities/$sub_tender_activity->slug";
                });
                return $this->renderXML('sitemaps.index', compact('sitemaps'));
        }

        if (str_contains($sitemap, 'latest-tenders-')) {
            $limit = 5000;
            $offset = (int) (explode('-', $sitemap)[2] - 1) * $limit;
            $urls = Tender::select(['updated_at', DB::raw("CONCAT('$url/$locale/tenders/', slug) AS url")])
                ->limit($limit)
                ->offset($offset)
                ->get();
        }
        return $this->renderXML('sitemaps.url', compact('urls'));
    }

    public function subTenderActivitiesSitemap($locale = 'ar', $subTenderActivity)
    {
        $url = env('FRONT_APP_URL');
        $subTenderActivity = SubTenderActivity::where('slug', $subTenderActivity)->first();
        if (!$subTenderActivity)
            return abort(404);
        $tender_activity_slug = $subTenderActivity?->activity?->slug;
        $tenders_count = Tender::where('sub_tender_activity_id', $subTenderActivity->id)->count();
        $pages_count = ceil($tenders_count / 12);

        $urls[] = ['url' => "$url/$locale/tender-activities/$tender_activity_slug/$subTenderActivity->slug"];
        for ($i = 2; $i <= $pages_count; $i++) {
            $urls[] = ['url' => "$url/$locale/tender-activities/$tender_activity_slug/$subTenderActivity->slug/$i"];
        }
        return $this->renderXML('sitemaps.url', compact('urls'));
    }

    private function renderXML(string $view, $args)
    {
        $output = '<?xml version="1.0" ?>' . PHP_EOL . view($view, $args)->render();

        return response($output)->header('Content-Type', 'application/xml');
    }
}