# KneadIt Agent Guidance

## Model and reasoning policy

- Use GPT-5.6 Sol for KneadIt work by default.
- Use medium reasoning for routine, clearly scoped slices.
- Use high reasoning for CI failures, architectural decisions, multi-file refactors, or unclear bugs.
- Use xhigh reasoning only when the task explicitly requests a deep audit.
- Do not change the model or reasoning level without explaining the change first.

## Branch naming

- Do not add a `codex/` prefix to KneadIt branches.
- Use the conventional `<type>/<short-description>` format: `feature/`, `feat/`, `fix/`, `hotfix/`, `refactor/`, `docs/`, `test/`, `chore/`, or `release/`.
- Keep descriptions lowercase, concise, and hyphen-separated. For example: `docs/boost-rules-and-docs-cleanup`.

## Git and pull requests

- Use `develop` as the integration branch and `main` as the release branch. Reserve `development` for environment names, not new branch names.
- Create focused working branches from an up-to-date `develop`; do not commit feature work directly to `develop` or `main`.
- Squash merge feature, fix, refactor, chore, docs, and test branches into `develop` through pull requests.
- Squash merge `hotfix/` branches into `main`; merge `release/` branches into `main` with regular merge commits. Do not rebase-merge pull requests.
- Before merging, verify the pull request's head branch, base branch, and merge method.
- Every new commit must follow [Conventional Commits 1.0.0](https://www.conventionalcommits.org/en/v1.0.0/): `type: description`, with an optional scope (`type(scope): description`) and optional breaking-change marker (`type(scope)!: description`).
- Use lowercase types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, or `revert`. Use `feat` for new features and `fix` for bug fixes; branch prefixes such as `feature/`, `hotfix/`, and `release/` are not commit types.
- Write a concise, imperative description. Mark breaking changes with `!` before the colon or a `BREAKING CHANGE: description` footer.
- Apply the same convention to pull request titles, squash commit subjects, and release or synchronization merge commit subjects; replace generated merge subjects when necessary (for example, `chore(release): release 2026.09.15`).
- Check the message before every commit and verify the final commit subject before merging a pull request. Do not rely on squash merging to excuse nonconforming feature-branch commits.
- Write pull request bodies as actual multiline Markdown. When using the GitHub CLI, prefer `--body-file` or a command input that preserves real newlines; never pass literal `\\n` sequences.
