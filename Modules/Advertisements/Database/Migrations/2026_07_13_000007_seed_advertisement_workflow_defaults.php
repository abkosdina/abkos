<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('advertisement_workflow_states')) {
            return;
        }

        if (!Schema::hasColumn('advertisement_workflow_states', 'key')) {
            Schema::table('advertisement_workflow_states', function ($table) {
                $table->string('key')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('advertisement_workflow_states', 'label')) {
            Schema::table('advertisement_workflow_states', function ($table) {
                $table->string('label')->nullable()->after('name');
            });
        }

        $states = [
            ['key' => 'Draft', 'name' => 'Draft'],
            ['key' => 'PendingReview', 'name' => 'Pending Review'],
            ['key' => 'NeedCorrection', 'name' => 'Need Correction'],
            ['key' => 'Rejected', 'name' => 'Rejected'],
            ['key' => 'Approved', 'name' => 'Approved'],
            ['key' => 'Published', 'name' => 'Published'],
            ['key' => 'Paused', 'name' => 'Paused'],
            ['key' => 'Expired', 'name' => 'Expired'],
            ['key' => 'Sold', 'name' => 'Sold'],
            ['key' => 'Archived', 'name' => 'Archived'],
            ['key' => 'Deleted', 'name' => 'Deleted'],
        ];

        foreach ($states as $s) {
            $payload = ['name' => $s['name']];
            if (Schema::hasColumn('advertisement_workflow_states', 'key')) {
                $payload['key'] = $s['key'];
            }
            if (Schema::hasColumn('advertisement_workflow_states', 'label')) {
                $payload['label'] = $s['name'];
            }
            DB::table('advertisement_workflow_states')->updateOrInsert(
                Schema::hasColumn('advertisement_workflow_states', 'key') ? ['key' => $s['key']] : ['name' => $s['name']],
                $payload
            );
        }

        if (!Schema::hasTable('advertisement_workflow_transitions')) {
            return;
        }

        if (!Schema::hasColumn('advertisement_workflow_transitions', 'key')) {
            Schema::table('advertisement_workflow_transitions', function ($table) {
                $table->string('key')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('advertisement_workflow_transitions', 'label')) {
            Schema::table('advertisement_workflow_transitions', function ($table) {
                $table->string('label')->nullable()->after('name');
            });
        }

        if (!Schema::hasColumn('advertisement_workflow_transitions', 'required_roles')) {
            Schema::table('advertisement_workflow_transitions', function ($table) {
                $table->json('required_roles')->nullable();
            });
        }

        if (!Schema::hasColumn('advertisement_workflow_transitions', 'role_required')) {
            Schema::table('advertisement_workflow_transitions', function ($table) {
                $table->string('role_required')->nullable();
            });
        }

        $transitions = [
            ['key' => 'submit', 'name' => 'Submit', 'from_state' => 'Draft', 'to_state' => 'PendingReview', 'role_required' => null],
            ['key' => 'approve', 'name' => 'Approve', 'from_state' => 'PendingReview', 'to_state' => 'Approved', 'role_required' => 'Operator'],
            ['key' => 'reject', 'name' => 'Reject', 'from_state' => 'PendingReview', 'to_state' => 'Rejected', 'role_required' => 'Operator'],
            ['key' => 'request_correction', 'name' => 'Request Correction', 'from_state' => 'PendingReview', 'to_state' => 'NeedCorrection', 'role_required' => 'Operator'],
            ['key' => 'publish', 'name' => 'Publish', 'from_state' => 'Approved', 'to_state' => 'Published', 'role_required' => 'Admin'],
            ['key' => 'pause', 'name' => 'Pause', 'from_state' => 'Published', 'to_state' => 'Paused', 'role_required' => 'Admin'],
            ['key' => 'resume', 'name' => 'Resume', 'from_state' => 'Paused', 'to_state' => 'Published', 'role_required' => 'Admin'],
            ['key' => 'mark_sold', 'name' => 'Mark As Sold', 'from_state' => 'Published', 'to_state' => 'Sold', 'role_required' => 'Admin'],
            ['key' => 'expire', 'name' => 'Expire', 'from_state' => 'Published', 'to_state' => 'Expired', 'role_required' => null],
            ['key' => 'archive_from_rejected', 'name' => 'Archive From Rejected', 'from_state' => 'Rejected', 'to_state' => 'Archived', 'role_required' => 'user,owner,operator,senior-operator,admin'],
            ['key' => 'archive_from_published', 'name' => 'Archive From Published', 'from_state' => 'Published', 'to_state' => 'Archived', 'role_required' => 'user,owner,operator,senior-operator,admin'],
            ['key' => 'restore', 'name' => 'Restore', 'from_state' => 'Archived', 'to_state' => 'Draft', 'role_required' => 'Senior Operator'],
        ];

        foreach ($transitions as $t) {
            $payload = [];

            if (Schema::hasColumn('advertisement_workflow_transitions', 'name')) {
                $payload['name'] = $t['name'];
            }

            if (Schema::hasColumn('advertisement_workflow_transitions', 'from_state')) {
                $payload['from_state'] = $t['from_state'];
            }

            if (Schema::hasColumn('advertisement_workflow_transitions', 'to_state')) {
                $payload['to_state'] = $t['to_state'];
            }

            if (Schema::hasColumn('advertisement_workflow_transitions', 'key')) {
                $payload['key'] = $t['key'];
            }

            if (Schema::hasColumn('advertisement_workflow_transitions', 'action')) {
                $payload['action'] = $t['key'];
            }

            if (Schema::hasColumn('advertisement_workflow_transitions', 'label')) {
                $payload['label'] = $t['name'];
            }

            if (Schema::hasColumn('advertisement_workflow_transitions', 'role_required')) {
                $payload['role_required'] = $t['role_required'];
            }

            if (empty($payload)) {
                continue;
            }

            $identifier = [];
            if (Schema::hasColumn('advertisement_workflow_transitions', 'key')) {
                $identifier['key'] = $t['key'];
            } elseif (Schema::hasColumn('advertisement_workflow_transitions', 'action')) {
                $identifier['action'] = $t['key'];
            }

            DB::table('advertisement_workflow_transitions')->updateOrInsert($identifier, $payload);
        }
    }

    public function down(): void
    {
        DB::table('advertisement_workflow_transitions')->whereIn('key', [
            'submit','approve','reject','request_correction','publish','pause','resume','mark_sold','expire','archive_from_rejected','archive_from_published','restore'
        ])->delete();

        DB::table('advertisement_workflow_states')->whereIn('key', [
            'Draft','PendingReview','NeedCorrection','Rejected','Approved','Published','Paused','Expired','Sold','Archived','Deleted'
        ])->delete();
    }
};
