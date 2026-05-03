# EZBBS TODOs (Scoped)

This TODO keeps work focused on core forum stability and usability.

Scope guardrails:
- Prioritize broken core flows over new features.
- Favor simple, maintainable fixes.
- Defer "extra flair" ideas until core posting/browsing/moderation is stable.
- Ignore `/tests` folder work (planned for removal before deployment).

## P0 - Core blockers (do first)

- [ ] Fix thread fetch naming mismatch (`do_getThreadById` vs `getThreadById`).
  - Files: `init.php`, `thread.php`, `new_reply.php`
  - Goal: Thread and reply pages can load reliably.

- [ ] Normalize homepage menu data shape.
  - Files: `init.php`, `index.php`, `thread.php`, `user.php`
  - Goal: Header menu renders correctly on all pages.

- [ ] Align topic field names with DB schema.
  - Files: `index.php`, `db/topics.sql`
  - Goal: Replace `reply_count`/`visit_count`/`category_name` usage with schema-consistent values.

- [ ] Fix unban path to call unban function.
  - Files: `do/unban.php`, `init.php`
  - Goal: Moderator unban action actually unbans.

- [ ] Add missing schema files/tables used by existing code.
  - Files: `db/` (add tables for `replies`, `post_flairs`, `awards`)
  - Goal: Existing queries stop failing on missing tables.

- [ ] Make `thread.php` minimally complete and valid.
  - Files: `thread.php`
  - Goal: Render thread page cleanly (topic + reply list + valid HTML structure).

- [ ] Make `new_reply.php` minimally complete.
  - Files: `new_reply.php`, `init.php`
  - Goal: Compose and submit a basic reply successfully.

## P1 - Core completion (still in scope)

- [ ] Repair `post_Reply` and `post_Topic` SQL/bind parameter consistency.
  - Files: `init.php`
  - Goal: Posting logic is consistent and does not fail due to SQL argument mismatches.

- [ ] Stabilize notifications (no advanced ticker work yet).
  - Files: `init.php`
  - Goal: Show one clear notification path first; remove early-return bugs and signature mismatch.

- [ ] Implement basic `do_logentry` output.
  - Files: `init.php` (+ optional simple log file)
  - Goal: Capture actionable Notice/Warning/Error events.

- [ ] Resolve page/category resolver dependency.
  - Files: `init.php`
  - Goal: Remove or implement `determine_current_page` dependency cleanly.

- [ ] Fix flair consensus function naming/return shape mismatch.
  - Files: `init.php`, `thread.php`
  - Goal: Thread consensus label renders without runtime errors.

## P2 - Nice-to-have polish (only after P0/P1)

- [ ] Add simple `.user_awards` layout styles.
  - Files: `assets/css/layout.css`, `user.php`

- [ ] Fix tag JSON search query.
  - Files: `init.php`

- [ ] Keep logout flow simple; defer logged-out notification system.
  - Files: `do/logout.php`, `init.php`

## Explicitly deferred (out of current scope)

- Full emoji conversion system.
- Complex mention ticker/marquee behavior.
- Large moderation route rewrites.
- New social/gamification mechanics beyond existing schema.

## Best starting place

Start with: **thread fetch naming mismatch** (`do_getThreadById` / `getThreadById`).

Why this first:
1. It is a small, low-risk change.
2. It unblocks two critical pages (`thread.php` and `new_reply.php`).
3. It gives a fast sanity check that routing + DB fetching are wired correctly before bigger edits.
