<?php

namespace Modules\Advertisements\DTO;

abstract class WorkflowActionDTO extends BaseDTO
{
    public ?string $reason = null;
    public ?string $comment = null;
    public ?array $metadata = null;
}

/**
 * Submit Advertisement DTO
 */
class SubmitAdvertisementDTO extends WorkflowActionDTO
{
}

/**
 * Approve Advertisement DTO
 */
class ApproveAdvertisementDTO extends WorkflowActionDTO
{
    public ?array $attachments = null;
}

/**
 * Reject Advertisement DTO
 */
class RejectAdvertisementDTO extends WorkflowActionDTO
{
    public string $description = '';
    public ?array $attachments = null;
}

/**
 * Correction Request DTO
 */
class CorrectionRequestDTO extends WorkflowActionDTO
{
    public string $description = '';
    public ?array $fields_to_correct = null;
    public ?array $attachments = null;
}

/**
 * Publish Advertisement DTO
 */
class PublishAdvertisementDTO extends WorkflowActionDTO
{
}

/**
 * Pause Advertisement DTO
 */
class PauseAdvertisementDTO extends WorkflowActionDTO
{
}

/**
 * Resume Advertisement DTO
 */
class ResumeAdvertisementDTO extends WorkflowActionDTO
{
}

/**
 * Archive Advertisement DTO
 */
class ArchiveAdvertisementDTO extends WorkflowActionDTO
{
}

/**
 * Restore Advertisement DTO
 */
class RestoreAdvertisementDTO extends WorkflowActionDTO
{
    public ?string $restore_to_state = 'Draft';
}

/**
 * Mark As Sold DTO
 */
class MarkAsSoldDTO extends WorkflowActionDTO
{
}

/**
 * Workflow Action Response DTO
 */
class WorkflowActionResponseDTO extends BaseDTO
{
    public bool $success = false;
    public string $message = '';
    public ?string $old_state = null;
    public ?string $new_state = null;
    public ?array $advertisement = null;
    public ?array $errors = null;
}
