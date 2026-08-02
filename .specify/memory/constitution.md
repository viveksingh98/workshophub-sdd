# WorkshopHub Constitution

Project-wide non-negotiables. Every feature spec, plan, and task list must comply.

## Stack (deliberately boring)

- Laravel monolith, server-rendered Blade views
- MySQL in production, SQLite for local quickstart and tests
- Native JavaScript only — no frontend frameworks, no CDN dependencies
- Theme CSS lives in `public/assets/css/themes/` — one file per theme

## Rules every feature must obey

1. **Spec before code.** No implementation without a reviewed `specs/<feature>/spec.md`.
2. **The spec is pure WHAT.** No framework names, table names, or routes inside `spec.md` — technology enters at plan time.
3. **Integrity at the database layer.** Concurrency-sensitive invariants (capacity, double-booking) are enforced inside a transaction, not only with application-level if-checks.
4. **Server-side validation for every write.** Form requests or explicit `validate()` calls; user-facing error messages.
5. **Tests ship with the feature.** Every acceptance criterion in the spec maps to at least one feature test. No hardcoded dates in tests — they go stale.
6. **Changes enter through the spec.** A change request updates `spec.md` first, then plan, tasks, and code are regenerated.

## Style

- Small controllers, focused services for domain rules
- Readable code over clever code; match the existing file's idiom
- Seeded demo data must make the app presentable on first boot
