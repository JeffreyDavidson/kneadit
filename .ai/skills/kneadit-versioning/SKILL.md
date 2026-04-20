---
name: kneadit-versioning
description: "KneadIt release-tagging discipline. Activate whenever the user mentions cutting a tag, releasing, version bumps, semver, or asks something like 'should we cut a release', 'what's next after this merge', or merges a release PR. Also activate when reviewing or opening a release PR (develop → main). Covers when a minor bump is justified vs a patch, when to skip a tag entirely, when a migration warrants its own release, and the historical anti-pattern of bumping minor for every PR cluster."
---

# KneadIt Release Versioning Rules

## Hard Rules

- **Do NOT cut a release after every merged PR.** Releases gate cumulative work; per-PR tagging produces noise like v1.2.0 → v1.3.0 → v1.4.0 → v1.5.0 within a single afternoon.
- **Do NOT default to bumping minor.** Most refactor work is a patch. Only bump minor for genuine new features or substantive feature-shaped milestones.
- **Don't reflexively cut a release after a "Merged" reply.** Ask whether the cumulative work since the last tag justifies a bump. Often the answer is "wait."

## SemVer mapping for this project

| Bump | When |
|---|---|
| **PATCH** (`1.x.Y`) | Refactor-only changes, internal consistency improvements, test additions, performance tweaks, dead-code removal, dependency updates that don't change behavior. Most of this codebase's work falls here. |
| **MINOR** (`1.Y.0`) | Genuinely new user-facing capabilities (new feature flag, new admin page, new payment method). Or a substantive milestone that bundles 6+ refactor PRs into one cohesive theme worth communicating. |
| **MAJOR** (`X.0.0`) | Breaking changes to a documented contract — public API changes, removal of a tenant-facing feature, schema changes that require manual data migration steps beyond `tenants:migrate`. |

## When to cut a release vs hold

**Cut a release when:**
- You have a tenant DB migration that needs to deploy and warrants a clean rollback reference point. Migrations are the strongest signal — always tag the release that ships them.
- You've batched a coherent themed body of work (a "sub-DTO migration sweep", "Money value object sweep") that's worth communicating as a unit.
- 4+ weeks have passed since the last tag and develop has shipped meaningful work.
- A release-worthy bug fix needs a stable reference for hotfix branches.

**Hold (don't cut) when:**
- Only 1–2 small refactor PRs have landed since the last tag.
- The work is purely internal cleanup with no externally observable change.
- The next planned PR is in the same theme — wait for that to land too.
- You just shipped a release within the last few hours and don't have a migration or breaking change that needs its own tag.

## When in doubt, ask the user

Don't auto-cut. After a refactor merge, the right default behavior is:

1. Sync develop locally.
2. Note what's accumulated since the last tag.
3. Ask the user whether to keep batching or release now — *unless* there's a tenant migration on develop, in which case recommend tagging.

## Release PR conventions

- Title: `release: vX.Y.Z — short theme`
- Body groups PRs by theme, summarizes the highlight per PR, calls out any migrations or deploy steps under a "Deploy steps" section.
- Always lead migration PRs with a ⚠️ marker so the deploy step isn't missed.

## Tag conventions

- Annotated tags only (`git tag -a vX.Y.Z -m "..."`).
- Tag message includes: one-line summary + bullet list of PRs + highlight section + deploy steps if any.
- Push tags explicitly (`git push origin vX.Y.Z`); never with `--tags`.

## Historical anti-pattern (avoid repeating)

In one session, v1.2.0 → v1.10.0 was bumped 9 times — most should have been patches, and several PR clusters didn't warrant any tag at all (e.g., the dashboard widget caching PRs would've been a single `v1.10.1` patch, or no tag at all). The trigger was "cut a release after every Merged" rather than evaluating cumulative-work-vs-version-semantics.

The pattern to break: don't propose a release PR right after a feature merge. Sync develop, hold, and wait for either accumulated themed work or an explicit user request.
