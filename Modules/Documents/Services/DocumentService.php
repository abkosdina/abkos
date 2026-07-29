<?php

namespace Modules\Documents\Services;

use Illuminate\Http\UploadedFile;
use Modules\Documents\Repositories\Interfaces\DocumentCategoryRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentLinkRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentMetadataRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentTagRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentTypeRepositoryInterface;
use Modules\Documents\Repositories\Interfaces\DocumentVersionRepositoryInterface;
use Modules\Documents\Services\HashService;
use Modules\Documents\Services\StorageService;
use Modules\Documents\Services\VersionService;

class DocumentService
{
    public function __construct(
        protected DocumentRepositoryInterface $documentRepository,
        protected DocumentTypeRepositoryInterface $typeRepository,
        protected DocumentCategoryRepositoryInterface $categoryRepository,
        protected DocumentTagRepositoryInterface $tagRepository,
        protected DocumentMetadataRepositoryInterface $metadataRepository,
        protected DocumentLinkRepositoryInterface $linkRepository,
        protected DocumentVersionRepositoryInterface $versionRepository,
        protected StorageService $storageService,
        protected HashService $hashService,
        protected VersionService $versionService,
    ) {
    }

    public function list(array $filters = []): array
    {
        return $this->documentRepository->all();
    }

    public function find(int|string $id): ?object
    {
        return $this->documentRepository->find($id);
    }

    public function upload(array $data): object
    {
        $file = $data['file'];

        if (!$file instanceof UploadedFile) {
            throw new \InvalidArgumentException('Uploaded file is required.');
        }

        $uuid = $this->hashService->generateUuid();
        $generatedName = $uuid . '.' . $file->getClientOriginalExtension();
        $path = $this->storageService->putFile('documents', $file, $generatedName);

        return $this->documentRepository->create([
            'uuid' => $uuid,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'document_type_id' => $data['document_type_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'owner_type' => $data['owner_type'],
            'owner_id' => $data['owner_id'],
            'uploaded_by' => $data['uploaded_by'] ?? null,
            'storage_provider' => $this->storageService->getDriver(),
            'mime_type' => $file->getClientMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'original_name' => $file->getClientOriginalName(),
            'generated_name' => $generatedName,
            'hash' => $this->hashService->generateFileHash($file),
            'size' => $file->getSize(),
            'visibility' => $data['visibility'] ?? 'private',
            'status' => $data['status'] ?? 'uploaded',
            'current_version' => 'v1',
        ]);
    }

    public function update(int|string $id, array $data): bool
    {
        return $this->documentRepository->update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        $document = $this->documentRepository->find($id);

        if ($document && $document->generated_name) {
            $this->storageService->delete('documents/' . $document->generated_name);
        }

        return $this->documentRepository->delete($id);
    }

    public function download(int|string $id, ?object $user = null, ?string $ipAddress = null, ?string $userAgent = null): string
    {
        $document = $this->documentRepository->find($id);

        if (!$document) {
            throw new \InvalidArgumentException('Document not found.');
        }

        return $this->storageService->url('documents/' . $document->generated_name);
    }

    public function replace(int|string $id, array $data): object
    {
        $file = $data['file'] ?? null;

        if (!$file instanceof UploadedFile) {
            throw new \InvalidArgumentException('Replacement file is required.');
        }

        $document = $this->documentRepository->find($id);

        if (!$document) {
            throw new \InvalidArgumentException('Document not found.');
        }

        $this->versionService->createVersionFromCurrentState($document, $data['notes'] ?? null);
        $this->storageService->delete('documents/' . $document->generated_name);

        $newUuid = $this->hashService->generateUuid();
        $newGeneratedName = $newUuid . '.' . $file->getClientOriginalExtension();
        $this->storageService->putFile('documents', $file, $newGeneratedName);

        $currentVersion = $document->current_version;
        $nextVersionNumber = 1;

        if (preg_match('/^v(\d+)$/', $currentVersion, $matches)) {
            $nextVersionNumber = (int) $matches[1] + 1;
        }

        $this->documentRepository->update($id, [
            'uuid' => $newUuid,
            'storage_provider' => $this->storageService->getDriver(),
            'mime_type' => $file->getClientMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'original_name' => $file->getClientOriginalName(),
            'generated_name' => $newGeneratedName,
            'hash' => $this->hashService->generateFileHash($file),
            'size' => $file->getSize(),
            'status' => 'uploaded',
            'current_version' => 'v' . $nextVersionNumber,
        ]);

        return $this->documentRepository->find($id);
    }

    public function getTypes(): array
    {
        return $this->typeRepository->all();
    }

    public function createType(array $data): object
    {
        return $this->typeRepository->create($data);
    }
}
