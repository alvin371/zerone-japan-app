# Endorse V2 disposable integration fixtures

These fixtures use only `CREATE TEMPORARY TABLE`, fixture-only inserts, and
read queries. Run them against a disposable MySQL 8 database, never a normal
production workflow.

```bash
ENDORSE_V2_TEST_DATABASE=1 \
ENDORSE_V2_APP_ROOT="$PWD" \
php tools/run_endorse_v2_fixture.php pagination

ENDORSE_V2_TEST_DATABASE=1 \
ENDORSE_V2_APP_ROOT="$PWD" \
php tools/run_endorse_v2_fixture.php baseline
```

Automated today:

- pagination cardinality fixture;
- proposed baseline-marker schema fixture;
- dry-run importer deterministic-manifest hash.

Still manual/staging-only:

- authenticated browser Back/Forward verification;
- AJAX late-response race verification;
- CI3 controller integration and authorization tests.
