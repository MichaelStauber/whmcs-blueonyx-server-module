# BlueOnyx / WHMCS Handover

## Current direction

BlueOnyx is treated as a WHMCS server module with its own customer-facing
actions and BlueOnyx login handoff.

Core principles:

- WHMCS remains the billing and order system.
- BlueOnyx owns the server-side hosting control panel.
- The module should remain self-contained in `modules/servers/blueonyx/`.
- Do not require changes to WHMCS core files for normal module operation.
- Keep release packaging reproducible and free of secrets.

## What is already in place

- `modules/servers/blueonyx/blueonyx.php`
- `modules/servers/blueonyx/CceApiClient.php`
- customer-side templates in `modules/servers/blueonyx/templates/`
- BlueOnyx login handoff via `autologin.php`
- module metadata in `modules/servers/blueonyx/whmcs.json`
- repository packaging files in the root directory
- release baseline: `3.0.0`

## What still needs attention

- Verify the module on a live WHMCS installation after any packaging change.
- Confirm login handoff still works against a real BlueOnyx node.
- Re-test account lifecycle actions after any API or WHMCS upgrade.
- Keep the customer templates aligned with current WHMCS theming.
- Re-run syntax checks after refactors in `blueonyx.php` and `CceApiClient.php`.

## Deployment reminder

Typical module path:

- `modules/servers/blueonyx/`

After deployment:

- clear WHMCS template caches if needed
- verify the module appears in the server module list
- confirm login and custom actions on an active service

## Rollback reminder

- restore the previous module directory from backup
- clear caches if necessary
- leave WHMCS billing data and services intact
