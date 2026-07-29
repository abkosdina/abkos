<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Documents\Http\Requests\StoreDocumentRequest;
use Modules\Documents\Http\Requests\UpdateDocumentRequest;
use Modules\Documents\Services\DocumentService;
use Modules\Documents\Services\DownloadService;
use Modules\Documents\Services\SharingService;
use Modules\Shared\Base\BaseController;

class DocumentController extends BaseController
{
    public function __construct(
        protected DocumentService $documentService,
        protected DownloadService $downloadService,
        protected SharingService $sharingService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->documentService->list($request->all()),
            'message' => 'Documents retrieved successfully.',
        ]);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->documentService->upload($request->validated()),
            'message' => 'Document uploaded successfully.',
        ]);
    }

    public function show($document): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->documentService->find($document),
            'message' => 'Document retrieved successfully.',
        ]);
    }

    public function update(UpdateDocumentRequest $request, $document): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->documentService->update($document, $request->validated()),
            'message' => 'Document updated successfully.',
        ]);
    }

    public function destroy($document): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->documentService->delete($document),
            'message' => 'Document deleted successfully.',
        ]);
    }

    public function download($document, Request $request): JsonResponse
    {
        $result = $this->documentService->download($document, $request->user(), $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Document download recorded successfully.',
        ]);
    }

    public function share(Request $request, $document): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->sharingService->createShareLink($document, null, $request->boolean('public', false)),
            'message' => 'Document share link generated successfully.',
        ]);
    }

    public function replace(Request $request, $document): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->documentService->replace($document, $request->all()),
            'message' => 'Document replaced successfully.',
        ]);
    }
}
