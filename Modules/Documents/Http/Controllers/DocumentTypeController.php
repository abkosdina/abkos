<?php

namespace Modules\Documents\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Documents\Http\Requests\StoreDocumentTypeRequest;
use Modules\Documents\Services\DocumentService;
use Modules\Shared\Base\BaseController;

class DocumentTypeController extends BaseController
{
    public function __construct(protected DocumentService $documentService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->documentService->getTypes(),
            'message' => 'Document types retrieved successfully.',
        ]);
    }

    public function store(StoreDocumentTypeRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->documentService->createType($request->validated()),
            'message' => 'Document type created successfully.',
        ]);
    }
}
