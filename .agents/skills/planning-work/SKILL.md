---
name: planning-work
description: Plans and stress-tests non-trivial software work before implementation. Use when the user asks for a plan, wants to compare interface or architecture options, asks to grill or pressure-test a decision, or when broad or risky work needs scope, acceptance criteria, verification, and risks before coding.
---

# Planning Work

Use this when the right next step is shaping the work before editing.

## Process

1. Determine the type of work to be done (feature, task, bugfix)
2. Determine the branch name (e.g. `feature/my-feature`)
3. Look for an existing plan in `./docs/agent-work/$TYPE/$BRANCH_NAME/PLAN.md`, if it doesn't exist, create it
4. Look for existing context in `./docs/agent-work/$TYPE/$BRANCH_NAME/CONTEXT.md`, if it doesn't exist, create it
5. Read project architecture and code quality docs
6. Read domain architecture and code quality docs
7. Use the security-review skill to review the plan
8. Identify acceptance criteria, likely files, verification, risks, and open questions.
9. Update the plan if implementation changes direction.

## PLAN.md

The `PLAN.md` file has the following structure:

```markdown
# Objective

Describe the goal of the work in plain language.

# Scope

Describe what is included in this work.

# Acceptance Criteria

— [ ] Criterion 1
— [ ] Criterion 2
— [ ] Criterion 3

# Phases

## Phase 1: Name

Goal:

Tasks:

— [ ] Task 1
— [ ] Task 2

## Phase 2: Name

Goal:

Tasks:

— [ ] Task 1
— [ ] Task 2

# Verification

Describe how the work should be verified.

— [ ] Run relevant tests
— [ ] Run static analysis
— [ ] Run linting/formatting
— [ ] Manually verify expected behaviour

# Risks

— Risk 1
— Risk 2

# Open Questions

— Question 1
— Question 2
```

## CONTEXT.md

The `CONTEXT.md` file is a living document that records important context discovered while planning or implementing the work.
Use this structure:

```markdown
# Context

## Summary

Briefly describe the work and current state.

## Decisions

Record important decisions.

— Decision:
  — Reason:
  — Date:

## Discoveries

Record important findings from reading the codebase or documentation.

— Discovery:
  — Source:
  — Impact:

## Changes in Direction

Record when the implementation plan changes.

— Change:
  — Previous approach:
  — New approach:
  — Reason:

## Blockers

Record anything preventing progress.

— Blocker:
  — Impact:
  — Possible resolution:

## Notes

Record any other useful context.
```