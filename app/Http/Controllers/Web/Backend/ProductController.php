<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Helpers\Helper;
use App\Models\Product;
use App\Models\BundleItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class ProductController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'product');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Product::query()->orderBy('id', 'desc')->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category', function ($data) {
                    return "Products";
                })
                ->addColumn('author', function ($data) {
                    return "<a href='" . route('admin.users.show', $data->user_id) . "'>" . $data->user->name . "</a>";
                })
                ->addColumn('title', function ($data) {
                    return Str::limit($data->title, 20);
                })
                ->addColumn('thumbnail', function ($data) {
                    $url = asset($data->thumbnail && file_exists(public_path($data->thumbnail)) ? $data->thumbnail : 'default/logo.svg');
                    return '<img src="' . $url . '" alt="image" style="width: 50px; max-height: 100px; margin-left: 20px;">';
                })
                ->addColumn('status', function ($data) {
                    $backgroundColor = $data->status == "active" ? '#4CAF50' : '#ccc';
                    $sliderTranslateX = $data->status == "active" ? '26px' : '2px';
                    $sliderStyles = "position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background-color: white; border-radius: 50%; transition: transform 0.3s ease; transform: translateX($sliderTranslateX);";

                    $status = '<div class="form-check form-switch" style="margin-left:40px; position: relative; width: 50px; height: 24px; background-color: ' . $backgroundColor . '; border-radius: 12px; transition: background-color 0.3s ease; cursor: pointer;">';
                    $status .= '<input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status" style="position: absolute; width: 100%; height: 100%; opacity: 0; z-index: 2; cursor: pointer;">';
                    $status .= '<span style="' . $sliderStyles . '"></span>';
                    $status .= '<label for="customSwitch' . $data->id . '" class="form-check-label" style="margin-left: 10px;"></label>';
                    $status .= '</div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                                <a href="#" type="button" onclick="goToEdit(' . $data->id . ')" class="btn btn-primary fs-14 text-white" title="edit">
                                    <i class="fa fa-edit"></i>
                                </a>

                                <a href="#" type="button" onclick="goToOpen(' . $data->id . ')" class="btn btn-success fs-14 text-white " title="view">
                                    <i class="fa fa-eye"></i>
                                </a>

                                <a href="#" type="button" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger fs-14 text-white" title="Delete">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>';
                })
                ->rawColumns(['category', 'author', 'title', 'thumbnail', 'status', 'action'])
                ->make();
        }
        return view("backend.layouts.product.index");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        $brands = Brand::where('status', 'active')->get();
        return view('backend.layouts.product.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'price'             => 'required|numeric|min:0',
            'description'       => 'required|string',
            'stock'             => 'required|numeric|min:0',
            'supply_days'       => 'required|numeric|min:0',
            'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            // 'brand_id'          => 'nullable|exists:brands,id',
            // 'category_id'       => 'nullable|exists:categories,id',
            'include_item'      => 'nullable|array',
            'include_item.*'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $product = new Product();

            $product->user_id = auth('web')->user()->id;

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = Helper::fileUpload($request->file('thumbnail'), 'product', time() . '_' . getFileName($request->file('thumbnail')));
            }

            $product->slug = Helper::makeSlug(Product::class, $data['title']);

            $product->title = $data['title'];
            $product->price = $data['price'];
            $product->thumbnail = $data['thumbnail'];
            $product->description = $data['description'];
            $product->stock = $data['stock'];
            $product->supply_days = $data['supply_days'];
            // $product->brand_id = $data['brand_id'];
            // $product->category_id = $data['category_id'];
            $product->save();

            if ($request->has('include_item')) {
                collect($request->include_item)
                    ->filter() // remove empty values
                    ->each(function ($item) use ($product) {
                        $product->bundleItems()->create([
                            'title' => $item
                        ]);
                    });
            }

            session()->put('t-success', 'product created successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.product.index')->with('t-success', 'product created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(product $product, $id)
    {
        $product = Product::with('user')->where('id', $id)->first();
        return view('backend.layouts.product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(product $product, $id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::where('status', 'active')->get();
        return view('backend.layouts.product.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title'             => 'required|max:250',
            'price'             => 'required|numeric|min:0',
            'stock'             => 'required|numeric|min:0',
            'supply_days'       => 'required|numeric|min:0',
            'description'       => 'required|string',
            'thumbnail'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            // 'category_id'       => 'required|exists:categories,id'
            'include_item'      => 'nullable|array',
            'include_item.*'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $data = $validator->validated();

            $product = Product::findOrFail($id);

            if ($request->hasFile('thumbnail')) {
                $data['thumbnail'] = Helper::fileUpload($request->file('thumbnail'), 'product', time() . '_' . getFileName($request->file('thumbnail')));
            }

            $product->title         = $data['title'];
            $product->price         = $data['price'];
            $product->stock         = $data['stock'];
            $product->supply_days   = $data['supply_days'];
            $product->thumbnail     = $data['thumbnail'] ?? $product->thumbnail;
            $product->description   = $data['description'];
            // $product->category_id = $data['category_id'];
            $product->save();


            $existingIds = $product->bundleItems()->pluck('id')->toArray();
            $submittedIds = [];

            if ($request->has('include_item')) {

                foreach ($request->include_item as $key => $value) {

                    if (in_array($key, $existingIds)) {
                        // ✅ Update existing item
                        $product->bundleItems()
                            ->where('id', $key)
                            ->update(['title' => $value]);

                        $submittedIds[] = $key;
                    } else {
                        // ✅ Create new item
                        if (!empty($value)) {
                            $product->bundleItems()->create([
                                'title' => $value
                            ]);
                        }
                    }
                }
            }

            // ✅ Delete removed items
            $itemsToDelete = array_diff($existingIds, $submittedIds);
            $product->bundleItems()->whereIn('id', $itemsToDelete)->delete();

            session()->put('t-success', 'product updated successfully');
        } catch (Exception $e) {

            session()->put('t-error', $e->getMessage());
        }

        return redirect()->route('admin.product.edit', $product->id)->with('t-success', 'product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = Product::findOrFail($id);

            if ($data->thumbnail && file_exists(public_path($data->thumbnail))) {
                Helper::fileDelete(public_path($data->thumbnail));
            }

            $data->delete();
            return response()->json([
                'status' => 't-success',
                'message' => 'Your action was successful!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Your action was successful!'
            ]);
        }
    }

    public function status(int $id): JsonResponse
    {
        $data = Product::findOrFail($id);
        if (!$data) {
            return response()->json([
                'status' => 't-error',
                'message' => 'Item not found.',
            ]);
        }
        $data->status = $data->status === 'active' ? 'inactive' : 'active';
        $data->save();
        return response()->json([
            'status' => 't-success',
            'message' => 'Your action was successful!',
        ]);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|max:191',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $products = Product::query()
            ->select('id', 'name', 'description')
            ->where('name', 'like', '%' . $request->q . '%')
            ->orWhere('description', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function filter(Request $request)
    {
        $sort_by = $request->sort_key;
        $name = $request->name;
        $min = $request->min;
        $max = $request->max;

        $products = Product::query();
        $products->where('status', 'active');

        if ($request->brands != null && $request->brands != "") {
            $brand_ids = explode(',', $request->brands);
            $products->whereIn('brand_id', $brand_ids);
        }

        if ($request->has('made_in') && $request->made_in != "") {
            $made_in = explode(',', $request->made_in);
            $products->whereIn('made_in', $made_in);
        }

        if ($name != null && $name != "") {
            $products->where('name', 'like', '%' . $name . '%');
        }

        if ($min != null && $min != "" && is_numeric($min)) {
            $products->where('unit_price', '>=', $min);
        }

        if ($max != null && $max != "" && is_numeric($max)) {
            $products->where('unit_price', '<=', $max);
        }

        /* switch ($sort_by) {
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;

            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;

            case 'new_arrival':
                $products->orderBy('created_at', 'desc');
                break;

            case 'popularity':
                $products->orderBy('num_of_sale', 'desc');
                break;

            case 'top_rated':
                $products->orderBy('rating', 'desc');
                break;

            default:
                $products->orderBy('created_at', 'desc');
                break;
        } */

        $products = $products->paginate(15);
        return response()->json($products);
    }
}
