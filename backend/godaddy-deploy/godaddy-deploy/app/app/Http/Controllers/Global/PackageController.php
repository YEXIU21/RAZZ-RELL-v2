<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Rating;

class PackageController extends Controller
{
    public function index(){
        try{
            $packages = Package::where('flag', '0')
                ->with(['ratings' => function($query) {
                    $query->where('status', 'active');
                }])
                ->withCount(['bookings' => function($query) {
                    $query->whereIn('status', ['pending', 'confirmed', 'ongoing', 'preparing', 'completed']);
                }])
                ->get()
                ->map(function($package) {
                    $avgRating = $package->ratings->avg('rating') ?? 0;
                    $package->rating = round($avgRating, 1);
                    $package->reviewsCount = $package->ratings->count();
                    $package->bookingsCount = $package->bookings_count;
                    
                    $package->price_info = [
                        'base_price' => $package->package_price,
                        'is_per_day' => $package->price_per_day,
                        'additional_day_price' => $package->price_increase_per_day
                    ];
                    
                    unset($package->ratings);
                    unset($package->bookings_count);
                    unset($package->price_per_day);
                    unset($package->price_increase_per_day);
                    
                    return $package;
                });

            return response()->json($packages);
        }
        catch(\Exception $e){
            \Log::error('Error fetching packages: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching packages: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
    
    public function addPackage(Request $request)
    {
        try {
            \Log::info('Received package data:', $request->all());
            
            $validator = Validator::make($request->all(), [
                'package_name' => 'required|string|max:255',
                'package_type' => 'required|string|max:255',
                'package_description' => 'required|string',
                'package_price' => 'required|numeric|min:0',
                'additional_price_percentage' => 'nullable|numeric|min:0|max:100',
                'packs' => 'required|integer|min:1',
                'package_inclusion' => 'required|string',
                'package_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'price_per_day' => 'required|in:0,1',
                'price_increase_per_day' => 'nullable|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'status' => 422
                ], 422);
            }

            $packageData = [
                'package_name' => $request->package_name,
                'package_type' => $request->package_type,
                'package_description' => $request->package_description,
                'package_price' => $request->package_price,
                'additional_price_percentage' => $request->additional_price_percentage ?? 0,
                'packs' => $request->packs,
                'package_inclusion' => $request->package_inclusion,
                'price_per_day' => $request->price_per_day,
                'price_increase_per_day' => $request->price_increase_per_day ?? 0,
                'status' => 'active',
                'flag' => '0'
            ];

            // Handle image upload
            if ($request->hasFile('package_image')) {
                $file = $request->file('package_image');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(32) . '.' . $extension;
                Storage::disk('public')->putFileAs('packages', $file, $filename);
                $packageData['package_image'] = 'packages/' . $filename;
            }

            \Log::info('Creating package with data:', $packageData);

            $package = Package::create($packageData);

            return response()->json([
                'message' => 'Package added successfully',
                'status' => 'success',
                'data' => $package
            ], 200);
            
        } catch (\Exception $e) {
            \Log::error('Error adding package: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Error adding package: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function updatePackage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'name' => 'required', 
                'eventType' => 'required',
                'price' => 'required',
                'additional_price_percentage' => 'nullable|numeric|min:0|max:100',
                'description' => 'required',
                'inclusions' => 'required',
                'packs' => 'required',
                'status' => 'required',
                'image' => 'nullable|image|mimes:jpeg,png,webp|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'status' => 422
                ], 422);
            }
            
            $image = null;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(32) . '.' . $extension;
                Storage::disk('public')->putFileAs('packages', $file, $filename);
                $image = 'packages/' . $filename;
            }

            $package = Package::find($request->id);

            $package->update([
                'package_name' => $request->name,
                'package_image' => $image ?? $package->package_image,
                'packs' => $request->packs,
                'package_description' => $request->description,
                'package_type' => $request->eventType,
                'package_price' => $request->price,
                'additional_price_percentage' => $request->additional_price_percentage ?? 0,
                'status' => $request->status,
                'package_inclusion' => $request->inclusions,
            ]);
            
            if(!$package){
                return response()->json([
                    'message' => 'Package not found', 
                    'status' => 404
                ], 404);
            }else{
                return response()->json([
                    'message' => 'Package updated successfully',
                    'status' => 200,
                    'data' => $package
                ], 200);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error validating request',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }

    }

    public function getPackageById($id)
    {
        $package = Package::findOrFail($id);

        // Get the latest rating and review count
        $latestRatings = Rating::where('package_id', $id)
            ->where('status', 'active')
            ->get();

        // Update package rating and review count
        $avgRating = $latestRatings->avg('rating') ?? 0;
        $package->rating = round($avgRating, 1);
        $package->reviews_count = $latestRatings->count();
        $package->save();

        return response()->json($package);
    }

    public function deletePackage(Request $request, $id)
    {
        try {
            $package = Package::find($id);
            
            if (!$package) {
                return response()->json([
                    'message' => 'Package not found',
                    'status' => 404
                ], 404);
            }

            // Delete the image file if it exists
            if ($package->package_image) {
                try {
                    Storage::disk('public')->delete($package->package_image);
                } catch (\Exception $e) {
                    // Log error but continue with deletion
                    \Log::error('Failed to delete package image: ' . $e->getMessage());
                }
            }
            
            $result = $package->update([
                'flag' => '1'
            ]);

            if (!$result) {
                throw new \Exception('Failed to delete package');
            }
            
            return response()->json([
                'message' => 'Package deleted successfully',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting package: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting package: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'package_name' => 'required|string|max:255',
                'package_description' => 'required|string',
                'package_price' => 'required|numeric|min:0|max:999999999999',
                'price_per_day' => 'boolean',
                'packs' => 'required|integer|min:1',
                'package_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($request->hasFile('package_image')) {
                $image = $request->file('package_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/packages', $imageName);
                $validatedData['package_image'] = 'packages/' . $imageName;
            }

            $validatedData['price_per_day'] = $request->has('price_per_day') ? true : false;

            $package = Package::create($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Package created successfully',
                'data' => $package
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create package: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'package_name' => 'required|string|max:255',
                'package_description' => 'required|string',
                'package_price' => 'required|numeric|min:0|max:999999999999',
                'price_per_day' => 'boolean',
                'packs' => 'required|integer|min:1',
                'package_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $package = Package::findOrFail($id);

            if ($request->hasFile('package_image')) {
                // Delete old image if exists
                if ($package->package_image) {
                    Storage::delete('public/' . $package->package_image);
                }
                
                $image = $request->file('package_image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/packages', $imageName);
                $validatedData['package_image'] = 'packages/' . $imageName;
            }

            $validatedData['price_per_day'] = $request->has('price_per_day') ? true : false;

            $package->update($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Package updated successfully',
                'data' => $package
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update package: ' . $e->getMessage()
            ], 500);
        }
    }
}
