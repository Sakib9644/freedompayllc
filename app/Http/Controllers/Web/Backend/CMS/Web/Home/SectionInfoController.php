<?php

namespace App\Http\Controllers\Web\Backend\CMS\Web\Home;

use App\Http\Controllers\Controller;
use App\Enums\PageEnum;
use App\Enums\SectionEnum;
use App\Helpers\Helper;
use App\Models\CMS;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Requests\CmsRequest;
use App\Services\CmsService;
use Illuminate\Support\Facades\Log;

class SectionInfoController extends Controller
{
    protected $cmsService;

    public $name = "home";
    public $section = "banner";
    public $page = PageEnum::HOME;
    public $item = SectionEnum::BANNER;

    public function __construct(CmsService $cmsService)
    {
        $this->cmsService = $cmsService;
    }
    /**
     * Display a listing of the resource.
     */
    public function store(Request $request, $page, $section)
    {
        try {
            $validatedData = $request->validate([
                'title'     => 'nullable|string',
                'sub_title' => 'nullable|string',
            ]);

            CMS::updateOrCreate(
                [
                    'page'    => $page,
                    'section' => $page . '-' . $section,
                    'slug'    => $section,
                ],
                [
                    'title'     => $validatedData['title'] ?? null,
                    'sub_title' => $validatedData['sub_title'] ?? null,
                ]
            );

            return redirect()->back()->with('success', 'Section info updated successfully.');
        } catch (\Exception $e) {
            Log::error("Error updating section info: " . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while updating section info.');
        }
    }
}
