# Legacy Workflow Engine Audit

## Legacy WorkflowEngine file location

- `Modules/Advertisements/Services/Workflow/WorkflowEngine.php`

## Summary

The legacy `Modules/Advertisements/Services/Workflow/WorkflowEngine.php` file is present in the repository, but no executable PHP code currently imports or instantiates this class. All active advertisement workflow behavior is now routed through the generic `App\Services\Workflow\WorkflowEngine` via `Modules\Advertisements\Adapters\AdvertisementWorkflowAdapter`.

## References found

### Documentation references

These are the only matches for the legacy class or legacy engine file path:

- `Modules/Advertisements/WORKFLOW_README.md`
- `Modules/Advertisements/WORKFLOW_DOCUMENTATION.md`
- `Modules/Advertisements/WORKFLOW_ARCHITECTURE_AUDIT.md`
- `Modules/Advertisements/WORKFLOW_PHASE_1_IMPLEMENTATION_PLAN.md`
- `Modules/Advertisements/IMPLEMENTATION_SUMMARY.md`
- `Modules/Advertisements/IMPLEMENTATION_GUIDE.md`
- `Modules/Advertisements/QUICK_REFERENCE.md`

### Code references

Active code references to `WorkflowEngine` in `Modules/Advertisements` are for the generic engine, not the legacy advertisement-specific engine:

- `Modules/Advertisements/Providers/AdvertisementWorkflowServiceProvider.php`
  - `use App\Services\Workflow\WorkflowEngine;`
  - resolves generic `WorkflowEngine` to inject into `AdvertisementWorkflowAdapter`
- `Modules/Advertisements/Adapters/AdvertisementWorkflowAdapter.php`
  - `use App\Services\Workflow\WorkflowEngine;`
  - uses the generic engine for definition lookup, instance creation, and transition execution

### Legacy code file content

The legacy file defines an advertisement-specific engine class:

- Namespace: `Modules\Advertisements\Services\Workflow`
- Class: `WorkflowEngine`
- Dependencies:
  - `WorkflowStateManager`
  - `WorkflowTransitionManager`
  - `AdvertisementWorkflowAdapter`

## Classes/components depending on the legacy workflow engine

No active classes or components currently depend on `Modules\Advertisements\Services\Workflow\WorkflowEngine`.

### Active dependency analysis

- `Modules/Advertisements/Services/AdvertisementWorkflowService.php`
  - depends on `AdvertisementWorkflowAdapter`
  - not dependent on legacy `WorkflowEngine`
- `Modules/Advertisements/Providers/AdvertisementWorkflowServiceProvider.php`
  - depends on generic `App\Services\Workflow\WorkflowEngine`
- `Modules/Advertisements/Adapters/AdvertisementWorkflowAdapter.php`
  - depends on generic `App\Services\Workflow\WorkflowEngine`

## Active vs unused dependencies

- Legacy `Modules/Advertisements/Services/Workflow/WorkflowEngine.php`: unused by PHP application code
- Generic `App\Services\Workflow\WorkflowEngine`: active and in use by the advertisement adapter/provider

## Migration status

- Migration to the generic workflow engine is already complete for application code paths.
- The legacy file remains only as a leftover artifact and documentation reference.

## Potential breaking changes

- Since no active code imports or uses the legacy engine class, removing it would not affect application runtime if the documentation references are also updated.
- The only potential break would be external developers or scripts that manually instantiate the class via the legacy namespace. No such references exist in the repository.
- Before removal, update all documentation and audit artifacts to avoid stale references.

## Recommended migration plan

1. Keep `Modules/Advertisements/Services/Workflow/WorkflowEngine.php` as deprecated legacy code until full validation is completed.
2. Add a deprecation notice in the legacy file header warning developers that it is no longer used by active code.
3. Remove or update documentation references to the legacy file/class.
4. Run full tests and route list verification. If all tests pass and no active references remain, delete the legacy file later.

## Suggested deprecation notice

Add a comment at the top of `Modules/Advertisements/Services/Workflow/WorkflowEngine.php` such as:

> `// DEPRECATED: This advertisement-specific workflow engine has been replaced by App\Services\Workflow\WorkflowEngine and is retained temporarily for backward compatibility.`

## Conclusion

- Legacy `Modules/Advertisements/Services/Workflow/WorkflowEngine.php` is unused by application code.
- The system currently uses the generic workflow engine exclusively for advertisement workflows.
- Keep the file for now, mark it deprecated, and remove it only after tests and route validation.
