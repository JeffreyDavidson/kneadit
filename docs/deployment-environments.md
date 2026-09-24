# Deployment environments

KneadIt follows a Gitflow-style release process. Branches describe the state of
the code, while Forge sites and Cloudflare DNS provide the deployment targets.

## Environment ownership

| Environment | Branch or revision | Marketing site | Application site |
| --- | --- | --- | --- |
| Staging | `develop` during normal integration; active `release/*` branch during release hardening | `staging.getkneadit.app` | `app-staging.getkneadit.app` |
| Production | `main` at the approved release commit | `getkneadit.app` | `app.getkneadit.app` |

Production tenant storefronts use the application site through the production
wildcard `*.getkneadit.app`. There is no staging tenant wildcard. Staging is
for reviewing the central marketing site and application; it must not receive
tenant storefront traffic.

## Branch responsibilities

- Feature, fix, refactor, chore, docs, and test branches start from `develop`
  and merge into `develop` through focused pull requests.
- `develop` is the integration line and normally deploys to both staging sites.
- When a release is being prepared, create `release/vX.Y.Z` from `develop`.
  During release hardening, point staging deployments at that release branch so
  reviewers validate the exact candidate without pulling in later development.
- After approval, merge the release branch into `main` with a regular merge,
  create and push the release tag, and deploy production from `main`.
- Merge the completed release branch back into `develop` when release-only
  fixes or configuration changes need to remain in the next development cycle.
- Hotfixes branch from `main`, merge into `main` and `develop`, and receive a
  new production tag.

## Why staging does not normally follow a tag

A production tag identifies an approved release that is already represented on
`main`. Deploying the latest production tag to staging is useful for rollback
verification or a production-parity check, but it does not review unreleased
work. Normal staging should therefore follow `develop`, or the active
`release/*` branch when a release candidate is under review.

## Release review sequence

1. Merge the intended work into `develop` and verify the staging deployments.
2. Create `release/vX.Y.Z` from the current `develop`.
3. Point staging sites at the release branch and run the release checklist.
4. Fix only release-blocking issues on the release branch.
5. Merge the release branch into `main`, tag the approved commit, and deploy
   production.
6. Merge release-only fixes back into `develop` and return staging to `develop`.

## Domain topology

- `getkneadit.app` — production marketing site.
- `app.getkneadit.app` — production application and production tenant routing.
- `*.getkneadit.app` — production tenant storefront wildcard on the application
  site only.
- `staging.getkneadit.app` — staging marketing site.
- `app-staging.getkneadit.app` — staging application.
- `app.staging.getkneadit.app` — legacy staging application alias retained during
  the hostname transition; remove it only after the new URL and its dependent
  configuration have been verified.
- `kneadit.app` — not a KneadIt domain and must not be provisioned.
- A staging tenant wildcard — intentionally not provisioned.

Forge and Cloudflare changes must preserve this topology. Before moving a
domain, verify the exact site, DNS record, and target hostname; never remove a
production tenant wildcard while it is still the only route for live tenants.
