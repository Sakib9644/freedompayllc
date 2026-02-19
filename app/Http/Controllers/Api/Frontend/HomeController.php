<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Http\Resources\CMSResource;
use App\Http\Resources\ProductResource;
use App\Models\CMS;
use App\Models\Product;
use App\Models\Setting;
use App\Traits\CMSData;

class HomeController extends Controller
{
    use CMSData;
    public function index()
    {
        $data = [];

        $banner                 = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::BANNER)->first();
        $hero                   = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::HERO)->first();
        $whats_make             = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::WHAT_MAKES->value)->get();
        $whats_make_section       = CMS::where('page', PageEnum::HOME)
            ->where('section', PageEnum::HOME->value . '-' . SectionEnum::WHAT_MAKES->value)
            ->where('slug', SectionEnum::WHAT_MAKES)->first();

        $supplyment             = CMS::where('page', PageEnum::HOME->value)->where('section', SectionEnum::SUPPLYMENT->value)->get();
        $supplyment_section       = CMS::where('page', PageEnum::HOME)
            ->where('section', PageEnum::HOME->value . '-' . SectionEnum::SUPPLYMENT->value)
            ->where('slug', SectionEnum::SUPPLYMENT)->first();
        $life_without           = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::LIFE_WITHOUT->value)->get();
        $life_without_section       = CMS::where('page', PageEnum::HOME)
            ->where('section', PageEnum::HOME->value . '-' . SectionEnum::LIFE_WITHOUT->value)
            ->where('slug', SectionEnum::LIFE_WITHOUT)->first();

        $founder_story          = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::FOUNDER_STORY->value)->first();
        $home_about             = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::ABOUT->value)->first();
        $settings               = Setting::first();

        $data['banner']             = $banner ? new CMSResource($banner) : null;
        $data['hero']               = $hero ? new CMSResource($hero) : null;

        $data['whats_make']         = [
            'section' => $whats_make_section ? new CMSResource($whats_make_section) : null,
            'data' => $whats_make ? CMSResource::collection($whats_make) : null
        ];
        $data['supplyment']         = [ 
            'section' => $supplyment_section ? new CMSResource($supplyment_section) : null,
            'data' => $supplyment ? CMSResource::collection($supplyment) : null
        ];
        $data['life_without']       = [ 
            'section' => $life_without_section ? new CMSResource($life_without_section) : null,
            'data' => $life_without ? CMSResource::collection($life_without) : null
        ];
        $data['founder_story']      = $founder_story ? new CMSResource($founder_story) : null;
        $data['home_about']         = $home_about ? new CMSResource($home_about) : null;
        $data['settings']           = $settings;

        return Helper::jsonResponse(true, 'Home Page', 200, $data);
    }
    public function educations()
    {
        $education_approach     = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::EDUCATION_APPROACH->value)->get();
        $education_approach_section       = CMS::where('page', PageEnum::HOME)
            ->where('section', PageEnum::HOME->value . '-' . SectionEnum::EDUCATION_APPROACH->value)
            ->where('slug', SectionEnum::EDUCATION_APPROACH)->first();
        $data = $education_approach ? CMSResource::collection($education_approach) : null;

        $data = $data->map(function ($data) {
            return [
                'id' => $data->id,
                'title' => $data->title,
                'sub_title' => $data->sub_title,
                'image' => asset($data->image),
            ];
        });

        return Helper::jsonResponse(true, 'Education Approach', 200, [
            'section' => $education_approach_section ? new CMSResource($education_approach_section) : null,
            'data' => $data
        ]);
    }
    public function educationsSingle($education_approach_id)
    {
        $education_approach     = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::EDUCATION_APPROACH->value)->where('id', $education_approach_id)->first();
        $data = $education_approach ? new CMSResource($education_approach) : null;

        return Helper::jsonResponse(true, 'Education Approach', 200, $data);
    }




    public function productIndex()
    {
        $data = [];

        $hero           = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::HERO)->first();
        $product     = Product::where('status', 'active')->first();
        $worktogether     = CMS::where('page', PageEnum::HOME->value)->where('section', SectionEnum::WORK_TOGETHER->value)->first();
        $life_without      = CMS::where('page', PageEnum::HOME)->where('section', SectionEnum::LIFE_WITHOUT->value)->get();


        $data['hero'] = $hero ? new CMSResource($hero) : null;
        $data['product'] = $product ? new ProductResource($product) : null;
        $data['work_together'] = $worktogether ? new CMSResource($worktogether) : null;
        $data['life_without'] = $life_without ? CMSResource::collection($life_without) : null;


        return Helper::jsonResponse(true, 'Home Page', 200, $data);
    }
}
