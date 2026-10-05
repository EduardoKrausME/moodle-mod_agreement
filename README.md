# Agreement

Agreement is a Moodle activity module for publishing a term, policy, declaration, or other text that requires each participant to record an explicit decision: **Agree** or **Disagree**.

The activity is designed for cases where a simple "viewed" flag is not enough. It keeps the learner's decision tied to the exact version of the text that was presented, so later edits do not rewrite the historical record.

## What the activity does

- Records an explicit **Agree** or **Disagree** response.
- Separates the capability to respond from the capability to only view the agreement.
- Allows one immutable response per participant and per agreement version.
- Creates a new version automatically when the agreement text or its format changes.
- Keeps previous responses attached to the original version instead of replacing them.
- Stores the date and time of each decision.
- Stores a SHA-256 content hash for every agreement version.
- Provides a teacher report with the current-version status, version history, and response history.
- Supports Moodle privacy APIs.
- Supports backup and restore, including version and response history when user data is included.
- Provides English and Brazilian Portuguese language packs.
- Uses Mustache templates and Moodle APIs without a custom renderer.

## Versioning behavior

Changing only the activity name or introduction does not create a new agreement version. Changing the agreement text or its text format creates the next version.

After a new version is published, participants must respond again. Their previous decision remains preserved against the version they originally received.

## Learner flow

The learner opens the activity, reads the current agreement text, and records one of the available decisions. Once recorded, that response is kept as the auditable decision for that learner and version rather than being silently replaced by later edits.

If the teacher publishes a new version, the learner sees the new text and can record a new decision for that version.

## Teacher report

Teachers with the appropriate capability can review the response status for the current version and inspect historical versions and responses. This makes it possible to distinguish learners who have not answered the current text from learners who answered an earlier version.

## Audit model

The plugin keeps agreement versions and responses as separate records. Each version stores its text, format, creation information, and content hash, while each learner response references the version that was active when the decision was made.

This model is intentionally simple: the plugin records what text version existed, who answered it, which decision was recorded, and when it happened.
