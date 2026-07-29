<?php

/**
 * @OA\Tag(
 *     name="Advertisements Workflow",
 *     description="Advertisement workflow state management and transitions"
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/submit",
 *     summary="Submit advertisement for review",
 *     description="Submit a draft advertisement for review by operators",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="reason", type="string", nullable=true, example="Ready for submission"),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Advertisement submitted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Advertisement submitted successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="Draft"),
 *                 @OA\Property(property="new_state", type="string", example="PendingReview"),
 *                 @OA\Property(
 *                     property="advertisement",
 *                     type="object",
 *                     ref="#/components/schemas/Advertisement"
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/approve",
 *     summary="Approve advertisement",
 *     description="Approve a pending review advertisement",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"reason"},
 *             @OA\Property(property="reason", type="string", example="All checks passed"),
 *             @OA\Property(property="comment", type="string", nullable=true, example="High quality listing"),
 *             @OA\Property(property="attachments", type="array", nullable=true, items={"type": "string"}),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Advertisement approved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Advertisement approved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="PendingReview"),
 *                 @OA\Property(property="new_state", type="string", example="Approved"),
 *                 @OA\Property(property="advertisement", type="object", ref="#/components/schemas/Advertisement")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/reject",
 *     summary="Reject advertisement",
 *     description="Reject a pending review advertisement",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"reason", "description"},
 *             @OA\Property(property="reason", type="string", example="Price too high"),
 *             @OA\Property(property="description", type="string", example="Market analysis shows this price is not competitive"),
 *             @OA\Property(property="attachments", type="array", nullable=true, items={"type": "string"}),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Advertisement rejected successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Advertisement rejected successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="PendingReview"),
 *                 @OA\Property(property="new_state", type="string", example="Rejected"),
 *                 @OA\Property(property="advertisement", type="object", ref="#/components/schemas/Advertisement")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - only operators can reject"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/correction",
 *     summary="Request advertisement correction",
 *     description="Request user to make corrections to a pending review advertisement",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"reason", "description"},
 *             @OA\Property(property="reason", type="string", example="Missing information"),
 *             @OA\Property(property="description", type="string", example="Please add more details about the loan terms"),
 *             @OA\Property(property="fields_to_correct", type="array", nullable=true, items={"type": "string"}),
 *             @OA\Property(property="attachments", type="array", nullable=true, items={"type": "string"}),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Correction request sent successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Correction requested successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="PendingReview"),
 *                 @OA\Property(property="new_state", type="string", example="NeedCorrection"),
 *                 @OA\Property(property="advertisement", type="object", ref="#/components/schemas/Advertisement")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - only operators can request corrections"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/publish",
 *     summary="Publish advertisement",
 *     description="Publish an approved advertisement (makes it visible in search)",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="reason", type="string", nullable=true),
 *             @OA\Property(property="comment", type="string", nullable=true),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Advertisement published successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Advertisement published successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="Approved"),
 *                 @OA\Property(property="new_state", type="string", example="Published"),
 *                 @OA\Property(property="advertisement", type="object", ref="#/components/schemas/Advertisement")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - only operators can publish"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Advertisement is not in approved state")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/pause",
 *     summary="Pause advertisement",
 *     description="Pause a published advertisement (removes from search temporarily)",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="reason", type="string", nullable=true),
 *             @OA\Property(property="comment", type="string", nullable=true),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Advertisement paused successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Advertisement paused successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="Published"),
 *                 @OA\Property(property="new_state", type="string", example="Paused"),
 *                 @OA\Property(property="advertisement", type="object", ref="#/components/schemas/Advertisement")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Advertisement is not published")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/resume",
 *     summary="Resume advertisement",
 *     description="Resume a paused advertisement (makes it visible again)",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="reason", type="string", nullable=true),
 *             @OA\Property(property="comment", type="string", nullable=true),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Advertisement resumed successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Advertisement resumed successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="Paused"),
 *                 @OA\Property(property="new_state", type="string", example="Published"),
 *                 @OA\Property(property="advertisement", type="object", ref="#/components/schemas/Advertisement")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Advertisement is not paused")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/archive",
 *     summary="Archive advertisement",
 *     description="Archive a published or rejected advertisement (becomes read-only)",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="reason", type="string", nullable=true),
 *             @OA\Property(property="comment", type="string", nullable=true),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Advertisement archived successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Advertisement archived successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="Published"),
 *                 @OA\Property(property="new_state", type="string", example="Archived"),
 *                 @OA\Property(property="advertisement", type="object", ref="#/components/schemas/Advertisement")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Advertisement cannot be archived from current state")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/restore",
 *     summary="Restore advertisement",
 *     description="Restore an archived advertisement",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="restore_to_state", type="string", nullable=true, enum={"Draft", "PendingReview", "Approved", "Published"}, example="Draft"),
 *             @OA\Property(property="reason", type="string", nullable=true),
 *             @OA\Property(property="comment", type="string", nullable=true),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Advertisement restored successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Advertisement restored successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="Archived"),
 *                 @OA\Property(property="new_state", type="string", example="Draft"),
 *                 @OA\Property(property="advertisement", type="object", ref="#/components/schemas/Advertisement")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden - only senior operators and admins can restore"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Advertisement is not archived")
 * )
 */

/**
 * @OA\Post(
 *     path="/api/advertisements/{uuid}/sold",
 *     summary="Mark advertisement as sold",
 *     description="Mark a published advertisement as sold",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="reason", type="string", nullable=true),
 *             @OA\Property(property="comment", type="string", nullable=true),
 *             @OA\Property(property="metadata", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Advertisement marked as sold successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Advertisement marked as sold successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="old_state", type="string", example="Published"),
 *                 @OA\Property(property="new_state", type="string", example="Sold"),
 *                 @OA\Property(property="advertisement", type="object", ref="#/components/schemas/Advertisement")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Advertisement not found"),
 *     @OA\Response(response=422, description="Advertisement is not published")
 * )
 */

/**
 * @OA\Get(
 *     path="/api/advertisements/{uuid}/workflow-state",
 *     summary="Get advertisement workflow state",
 *     description="Get current workflow state information for an advertisement",
 *     tags={"Advertisements Workflow"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="uuid",
 *         in="path",
 *         required=true,
 *         description="Advertisement UUID",
 *         @OA\Schema(type="string", format="uuid")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Workflow state retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="uuid", type="string", format="uuid"),
 *                 @OA\Property(property="current_state", type="string", example="Published"),
 *                 @OA\Property(property="state_label", type="string", example="Published"),
 *                 @OA\Property(property="created_at", type="string", format="date-time"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=401, description="Unauthorized"),
 *     @OA\Response(response=404, description="Advertisement not found")
 * )
 */

/**
 * @OA\Schema(
 *     schema="Advertisement",
 *     title="Advertisement",
 *     description="Advertisement resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="uuid", type="string", format="uuid"),
 *     @OA\Property(property="advertisement_number", type="string"),
 *     @OA\Property(property="title", type="string", example="BMW X5 for Sale"),
 *     @OA\Property(property="slug", type="string", example="bmw-x5-for-sale-1"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="short_description", type="string"),
 *     @OA\Property(property="price", type="number", format="float"),
 *     @OA\Property(property="currency", type="string", example="USD"),
 *     @OA\Property(property="status", type="string", enum={"Draft", "PendingReview", "NeedCorrection", "Rejected", "Approved", "Published", "Paused", "Expired", "Sold", "Archived", "Deleted"}),
 *     @OA\Property(property="visibility", type="string", example="public"),
 *     @OA\Property(property="published_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="views_count", type="integer"),
 *     @OA\Property(property="contacts_count", type="integer"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
 * )
 */
