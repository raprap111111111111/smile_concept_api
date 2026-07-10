<?php

namespace App\Http\Controllers\Api;

use App\Domain\Galleries\Actions\BulkDeleteGalleryAction;
use App\Domain\Galleries\Actions\CreateGalleryAction;
use App\Domain\Galleries\Actions\DeleteGalleryAction;
use App\Domain\Galleries\Actions\GetGalleriesAction;
use App\Domain\Galleries\Actions\UpdateGalleryAction;
use App\Domain\Galleries\DTOs\GalleryData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Galleries\StoreGalleryRequest;
use App\Http\Requests\Galleries\UpdateGalleryRequest;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function __construct(
        private readonly GetGalleriesAction $getGalleriesAction,
        private readonly CreateGalleryAction $createGalleryAction,
        private readonly UpdateGalleryAction $updateGalleryAction,
        private readonly DeleteGalleryAction $deleteGalleryAction,
        private readonly BulkDeleteGalleryAction $bulkDeleteGalleryAction,
    ) {}

    public function publicIndex(Request $request): JsonResponse
    {
        $galleries = $this->getGalleriesAction->execute(
            filters: [
                'active_only' => true,
                'featured_only' => $request->boolean('featured_only'),
                'category' => $request->category,
                'limit' => $request->limit,
            ]
        );

        return response()->json([
            'success' => true,
            'categories' => Gallery::categories(),
            'data' => $galleries,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $galleries = $this->getGalleriesAction->execute(
            filters: [
                'search' => $request->search,
                'category' => $request->category,
            ],
            paginate: true,
            perPage: $request->per_page ?? 20
        );

        return response()->json([
            'success' => true,
            'categories' => Gallery::categories(),
            'data' => $galleries,
        ]);
    }

    public function show(Gallery $gallery): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $gallery,
        ]);
    }

    public function store(StoreGalleryRequest $request): JsonResponse
    {
        $gallery = $this->createGalleryAction->execute(
            GalleryData::fromArray($request->validated() + ['image' => $request->file('image')])
        );

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => $gallery,
        ], 201);
    }

    public function update(UpdateGalleryRequest $request, Gallery $gallery): JsonResponse
    {
        $updated = $this->updateGalleryAction->execute(
            $gallery,
            GalleryData::fromArray($request->validated() + ['image' => $request->file('image')])
        );

        return response()->json([
            'success' => true,
            'message' => 'Gallery updated successfully',
            'data' => $updated,
        ]);
    }

    public function destroy(Gallery $gallery): JsonResponse
    {
        $this->deleteGalleryAction->execute($gallery);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:galleries,id',
        ]);

        $count = $this->bulkDeleteGalleryAction->execute($validated['ids']);

        return response()->json([
            'success' => true,
            'message' => "{$count} items deleted successfully",
        ]);
    }
}
