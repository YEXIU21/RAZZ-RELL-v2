<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Portfolio;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PortfolioController extends Controller
{
    public function allPortfolios()
    {
        $portfolios = Portfolio::where('flag', '0')->get();
        
        $portfolios = $portfolios->map(function ($portfolio) {
            return [
                'id' => $portfolio->id,
                'title' => $portfolio->title,
                'description' => $portfolio->description,
                'event_type' => $portfolio->event_type,
                'main_image_url' => $portfolio->main_image_url,
                'images_url' => $portfolio->images_url ? json_decode($portfolio->images_url) : [],
                'status' => $portfolio->status,
                'created_at' => $portfolio->created_at,
                'updated_at' => $portfolio->updated_at
            ];
        });
        
        return response()->json($portfolios);
    }

    public function addPortfolio(Request $request)
    {
        $validator = validator($request->all(), [
            "title" => 'required',
            "description" => 'required',
            "event_type" => "required",
            "main_image_url" => "required|image|mimes:jpeg,png,jpg,webp|max:5120",
            "album_images.*" => "nullable|image|mimes:jpeg,png,jpg,webp|max:5120"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Handle main image upload
            $mainImage = null;
            if ($request->hasFile('main_image_url')) {
                $file = $request->file('main_image_url');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(32) . '.' . $extension;
                Storage::disk('public')->putFileAs('portfolios', $file, $filename);
                $mainImage = 'portfolios/' . $filename;
            }

            // Handle album images
            $albumImages = [];
            if ($request->hasFile('album_images')) {
                foreach ($request->file('album_images') as $image) {
                    $extension = $image->getClientOriginalExtension();
                    $filename = Str::random(32) . '.' . $extension;
                    Storage::disk('public')->putFileAs('portfolio_albums', $image, $filename);
                    $albumImages[] = 'portfolio_albums/' . $filename;
                }
            }

            $portfolio = Portfolio::create([
                "title" => $request->title,
                "description" => $request->description,
                "event_type" => $request->event_type,
                "main_image_url" => $mainImage,
                "images_url" => json_encode($albumImages),
                "status" => "active",
                "flag" => "0"
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Portfolio added successfully',
                'status' => 200,
                'data' => [
                    'id' => $portfolio->id,
                    'title' => $portfolio->title,
                    'description' => $portfolio->description,
                    'event_type' => $portfolio->event_type,
                    'main_image_url' => $portfolio->main_image_url,
                    'images_url' => $albumImages,
                    'status' => $portfolio->status,
                    'created_at' => $portfolio->created_at,
                    'updated_at' => $portfolio->updated_at
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); 
            return response()->json([
                'message' => 'Failed to add portfolio: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function updatePortfolio(Request $request)
    {
        $validator = validator($request->all(), [
            'id' => 'required|exists:portfolios,id',
            'title' => 'required',
            'description' => 'required',
            'event_type' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'album_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'status' => 422
            ], 422);
        }

        try {
            DB::beginTransaction();

            $portfolio = Portfolio::findOrFail($request->id);

            // Handle cover image
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = Str::random(32) . '.' . $extension;
                Storage::disk('public')->putFileAs('portfolios', $file, $filename);

                // Delete old image if exists
                if ($portfolio->main_image_url) {
                    Storage::disk('public')->delete($portfolio->main_image_url);
                }

                $portfolio->main_image_url = 'portfolios/' . $filename;
            }

            // Update portfolio details
            $portfolio->title = $request->title;
            $portfolio->description = $request->description;
            $portfolio->event_type = $request->event_type;

            // Handle album images
            $currentImages = [];
            if ($request->has('existing_images')) {
                $currentImages = json_decode($request->existing_images) ?? [];
            }

            // Handle removed images
            if ($request->has('removed_images')) {
                $removedImages = json_decode($request->removed_images) ?? [];
                foreach ($removedImages as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            // Add new images
            if ($request->hasFile('album_images')) {
                $albumImages = $request->file('album_images');
                foreach ($albumImages as $image) {
                    $extension = $image->getClientOriginalExtension();
                    $filename = Str::random(32) . '.' . $extension;
                    Storage::disk('public')->putFileAs('portfolio_albums', $image, $filename);
                    $currentImages[] = 'portfolio_albums/' . $filename;
                }
            }

            // Update the portfolio with the final list of images
            $portfolio->images_url = json_encode($currentImages);
            $portfolio->save();

            DB::commit();

            return response()->json([
                'message' => 'Portfolio updated successfully',
                'status' => 200,
                'data' => [
                    'id' => $portfolio->id,
                    'title' => $portfolio->title,
                    'description' => $portfolio->description,
                    'event_type' => $portfolio->event_type,
                    'main_image_url' => $portfolio->main_image_url,
                    'images_url' => json_decode($portfolio->images_url) ?: [],
                    'status' => $portfolio->status,
                    'created_at' => $portfolio->created_at,
                    'updated_at' => $portfolio->updated_at
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update portfolio: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function deletePortfolio($id)
    {
        try {
            DB::beginTransaction();

            $portfolio = Portfolio::findOrFail($id);

            // Delete main image if it exists
            if ($portfolio->main_image_url) {
                Storage::disk('public')->delete($portfolio->main_image_url);
            }

            // Delete album images if they exist
            if ($portfolio->images_url) {
                $albumImages = json_decode($portfolio->images_url) ?: [];
                foreach ($albumImages as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $portfolio->flag = '1';
            $portfolio->save();

            DB::commit();

            return response()->json([
                'message' => 'Portfolio deleted successfully',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete portfolio: ' . $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
