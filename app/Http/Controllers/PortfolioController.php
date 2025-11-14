<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\PortFolio as Portfolio;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class PortfolioController extends Controller
{
   
    public function index(): JsonResponse
    {
        $items = Portfolio::orderBy('created_at', 'desc')->get();
        
        
        return response()->json($items->map(function($item) {
            return [
                'id' => (string)$item->id,
                'title' => $item->title,
                'category' => $item->category,
                'description' => $item->description,
                'imageUrl' => !$item->is_video ? $item->url : null,
                'videoUrl' => $item->is_video ? $item->url : null,
            ];
        }));
    }

   
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|file|max:204800|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,wmv,flv,webm', // 200MB max
                'category' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            $file = $request->file('file');
            
            if (!$file) {
                return response()->json([
                    'message' => 'No file uploaded',
                    'error' => 'FILE_MISSING'
                ], 400);
            }

            if (!$file->isValid()) {
                return response()->json([
                    'message' => 'File upload failed: ' . $file->getErrorMessage(),
                    'error' => 'FILE_INVALID'
                ], 400);
            }

            $mimeType = $file->getClientMimeType();
            $isVideo = str_starts_with($mimeType, 'video/');
            
           
            $path = $file->store('public/portfolio');
            
            if (!$path) {
                return response()->json([
                    'message' => 'Failed to store file',
                    'error' => 'STORAGE_FAILED'
                ], 500);
            }
            
            $portfolio = Portfolio::create([
                'title' => $request->input('title', $file->getClientOriginalName()),
                'category' => $request->input('category', 'Other'),
                'description' => $request->input('description'),
                'file_path' => $path,
                'mime_type' => $mimeType,
                'is_video' => $isVideo,
            ]);

            return response()->json([
                'id' => (string)$portfolio->id,
                'title' => $portfolio->title,
                'category' => $portfolio->category,
                'description' => $portfolio->description,
                'imageUrl' => !$isVideo ? $portfolio->url : null,
                'videoUrl' => $isVideo ? $portfolio->url : null,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Portfolio upload failed: ' . $e->getMessage(), [
                'file' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'none',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage(),
                'error' => 'UPLOAD_FAILED'
            ], 500);
        }
    }

    
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $portfolio = Portfolio::findOrFail($id);
            
            $request->validate([
                'file' => 'nullable|file|max:204800|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,wmv,flv,webm', // 200MB max
                'category' => 'nullable|string|max:255',
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            
            if ($request->has('title')) {
                $portfolio->title = $request->input('title');
            }
            if ($request->has('category')) {
                $portfolio->category = $request->input('category');
            }
            if ($request->has('description')) {
                $portfolio->description = $request->input('description');
            }

            
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                
                if (!$file->isValid()) {
                    return response()->json([
                        'message' => 'File upload failed: ' . $file->getErrorMessage(),
                        'error' => 'FILE_INVALID'
                    ], 400);
                }

                
                if (Storage::exists($portfolio->file_path)) {
                    Storage::delete($portfolio->file_path);
                }

               
                $mimeType = $file->getClientMimeType();
                $isVideo = str_starts_with($mimeType, 'video/');
                $path = $file->store('public/portfolio');
                
                if (!$path) {
                    return response()->json([
                        'message' => 'Failed to store file',
                        'error' => 'STORAGE_FAILED'
                    ], 500);
                }

                $portfolio->file_path = $path;
                $portfolio->mime_type = $mimeType;
                $portfolio->is_video = $isVideo;
            }

            $portfolio->save();

            return response()->json([
                'id' => (string)$portfolio->id,
                'title' => $portfolio->title,
                'category' => $portfolio->category,
                'description' => $portfolio->description,
                'imageUrl' => !$portfolio->is_video ? $portfolio->url : null,
                'videoUrl' => $portfolio->is_video ? $portfolio->url : null,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Portfolio item not found',
                'error' => 'NOT_FOUND'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Portfolio update failed: ' . $e->getMessage(), [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'message' => 'Update failed: ' . $e->getMessage(),
                'error' => 'UPDATE_FAILED'
            ], 500);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $portfolio = Portfolio::findOrFail($id);
            
            
            if (Storage::exists($portfolio->file_path)) {
                Storage::delete($portfolio->file_path);
            }
            
            $portfolio->delete();

            return response()->json(['message' => 'Portfolio item deleted'], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Portfolio item not found',
                'error' => 'NOT_FOUND'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Portfolio delete failed: ' . $e->getMessage(), [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Delete failed: ' . $e->getMessage(),
                'error' => 'DELETE_FAILED'
            ], 500);
        }
    }
}
