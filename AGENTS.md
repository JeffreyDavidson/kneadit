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

- Use conventional type-based branch names, such as `chore/remove-claude-specific-files`.
- Use a conventional commit type in branch names and commit messages: `feat`, `fix`, `refactor`, `chore`, `docs`, or `test`.
- Write pull request bodies as actual multiline Markdown. When using the GitHub CLI, prefer `--body-file` or a command input that preserves real newlines; never pass literal `\\n` sequences.
