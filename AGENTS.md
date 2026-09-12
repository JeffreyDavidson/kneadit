# KneadIt Agent Guidance

## Model and reasoning policy

- Use GPT-5.6 Sol for KneadIt work by default.
- Use medium reasoning for routine, clearly scoped slices.
- Use high reasoning for CI failures, architectural decisions, multi-file refactors, or unclear bugs.
- Use xhigh reasoning only when the task explicitly requests a deep audit.
- Do not change the model or reasoning level without explaining the change first.
