<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workflow_definitions') && ! Schema::hasColumn('workflow_definitions', 'slug')) {
            Schema::table('workflow_definitions', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('name');
            });
        }

        if (Schema::hasTable('workflow_transitions') && ! Schema::hasColumn('workflow_transitions', 'key')) {
            Schema::table('workflow_transitions', function (Blueprint $table) {
                $table->string('key')->nullable()->after('name')->index();
            });
        }

        if (Schema::hasTable('workflow_transitions') && ! Schema::hasColumn('workflow_transitions', 'from_state_id')) {
            Schema::table('workflow_transitions', function (Blueprint $table) {
                $table->foreignId('from_state_id')->nullable()->after('workflow_definition_id')->constrained('workflow_states')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('workflow_transitions') && ! Schema::hasColumn('workflow_transitions', 'to_state_id')) {
            Schema::table('workflow_transitions', function (Blueprint $table) {
                $table->foreignId('to_state_id')->nullable()->after('from_state_id')->constrained('workflow_states')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('workflow_transitions') && ! Schema::hasColumn('workflow_transitions', 'is_active')) {
            if (Schema::hasColumn('workflow_transitions', 'description')) {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->boolean('is_active')->default(true)->after('description');
                });
            } else {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->boolean('is_active')->default(true)->after('name');
                });
            }
        }

        if (Schema::hasTable('workflow_transitions') && ! Schema::hasColumn('workflow_transitions', 'required_role')) {
            Schema::table('workflow_transitions', function (Blueprint $table) {
                $table->string('required_role')->nullable()->after('is_active');
            });
        }

        if (Schema::hasTable('workflow_transitions') && ! Schema::hasColumn('workflow_transitions', 'required_permission')) {
            Schema::table('workflow_transitions', function (Blueprint $table) {
                $table->string('required_permission')->nullable()->after('required_role');
            });
        }

        if (Schema::hasTable('workflow_transitions') && ! Schema::hasColumn('workflow_transitions', 'configuration')) {
            Schema::table('workflow_transitions', function (Blueprint $table) {
                $table->json('configuration')->nullable()->after('required_permission');
            });
        }

        if (Schema::hasTable('workflow_transitions') && ! Schema::hasColumn('workflow_transitions', 'metadata')) {
            Schema::table('workflow_transitions', function (Blueprint $table) {
                $table->json('metadata')->nullable()->after('configuration');
            });
        }

        if (Schema::hasTable('workflow_instance_steps') && ! Schema::hasColumn('workflow_instance_steps', 'transition_id')) {
            Schema::table('workflow_instance_steps', function (Blueprint $table) {
                $table->foreignId('transition_id')->nullable()->after('workflow_instance_id')->constrained('workflow_transitions')->onDelete('restrict');
            });
        }

        if (Schema::hasTable('workflow_instance_steps') && ! Schema::hasColumn('workflow_instance_steps', 'from_state_id')) {
            Schema::table('workflow_instance_steps', function (Blueprint $table) {
                $table->foreignId('from_state_id')->nullable()->after('transition_id')->constrained('workflow_states')->onDelete('restrict');
            });
        }

        if (Schema::hasTable('workflow_instance_steps') && ! Schema::hasColumn('workflow_instance_steps', 'to_state_id')) {
            Schema::table('workflow_instance_steps', function (Blueprint $table) {
                $table->foreignId('to_state_id')->nullable()->after('from_state_id')->constrained('workflow_states')->onDelete('restrict');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('workflow_instance_steps')) {
            if (Schema::hasColumn('workflow_instance_steps', 'transition_id')) {
                Schema::table('workflow_instance_steps', function (Blueprint $table) {
                    $table->dropForeign(['transition_id']);
                    $table->dropColumn('transition_id');
                });
            }
            if (Schema::hasColumn('workflow_instance_steps', 'from_state_id')) {
                Schema::table('workflow_instance_steps', function (Blueprint $table) {
                    $table->dropForeign(['from_state_id']);
                    $table->dropColumn('from_state_id');
                });
            }
            if (Schema::hasColumn('workflow_instance_steps', 'to_state_id')) {
                Schema::table('workflow_instance_steps', function (Blueprint $table) {
                    $table->dropForeign(['to_state_id']);
                    $table->dropColumn('to_state_id');
                });
            }
        }

        if (Schema::hasTable('workflow_transitions')) {
            if (Schema::hasColumn('workflow_transitions', 'key')) {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->dropColumn('key');
                });
            }
            if (Schema::hasColumn('workflow_transitions', 'from_state_id')) {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->dropForeign(['from_state_id']);
                    $table->dropColumn('from_state_id');
                });
            }
            if (Schema::hasColumn('workflow_transitions', 'to_state_id')) {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->dropForeign(['to_state_id']);
                    $table->dropColumn('to_state_id');
                });
            }
            if (Schema::hasColumn('workflow_transitions', 'is_active')) {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->dropColumn('is_active');
                });
            }
            if (Schema::hasColumn('workflow_transitions', 'required_role')) {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->dropColumn('required_role');
                });
            }
            if (Schema::hasColumn('workflow_transitions', 'required_permission')) {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->dropColumn('required_permission');
                });
            }
            if (Schema::hasColumn('workflow_transitions', 'configuration')) {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->dropColumn('configuration');
                });
            }
            if (Schema::hasColumn('workflow_transitions', 'metadata')) {
                Schema::table('workflow_transitions', function (Blueprint $table) {
                    $table->dropColumn('metadata');
                });
            }
        }

        if (Schema::hasTable('workflow_definitions') && Schema::hasColumn('workflow_definitions', 'slug')) {
            Schema::table('workflow_definitions', function (Blueprint $table) {
                $table->dropColumn('slug');
            });
        }
    }
};
