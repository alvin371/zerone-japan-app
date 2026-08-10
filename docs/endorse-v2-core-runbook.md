# Endorse V2 Core rollout

V2 is additive and disabled by default. Deploying the code must not change the legacy
writer. Do not run the schema script or enable an environment flag as part of a normal
application deploy.

## Preconditions

1. Rehearse `migrations/create_endorse_v2_core.sql` on a disposable MySQL 8.0 database.
2. Capture provider fixtures and run the PHP 8.2 test harness in the target image.
3. Inventory and quiesce legacy cron, queue workers, scraper callbacks, and external callers.
4. Verify backup/restore and a rollback deploy.

## Controlled activation

1. Apply the additive schema in staging only.
2. Set the V2 environment flags only after the V2 runtime-control row has been reviewed.
3. Set `writer_mode=v2` only after legacy writer quiescence is proven. This is the
   single-writer switch; it causes guarded legacy writers to reject writes.
4. Configure a signed POST caller for `/api/internal/endorse-v2/tick`. The signature is
   HMAC-SHA256 of `timestamp + "\n" + HTTP method + "\n" + request URI`, with a five-minute clock window.
5. Keep Analytics, Threads, and Optimization disabled until their independent gates pass.

## Rollback

Before returning to legacy code, stop V2 workers, set `writer_mode=legacy`, and preserve
the V2 sidecar tables for audit. Dropping the tables is a separate, backup-verified action
using `migrations/rollback_create_endorse_v2_core.sql`.
