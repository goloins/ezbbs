# EZBBS TODOs (Scoped)

This TODO keeps work focused on core forum stability and usability.

Scope guardrails:
- Prioritize broken core flows over new features.
- Favor simple, maintainable fixes.
- Defer "extra flair" ideas until core posting/browsing/moderation is stable.
- Ignore `/tests` folder work (planned for removal before deployment).

## P0 - Core blockers (do first)

- [x] Fix cookie/session login typo in bootstrap.
  - Files: `init.php`
  - Goal: Replace `$COOKIE` with `$_COOKIE` so persistent login can work.

- [x] Fix banned-poster function call casing on homepage.
  - Files: `index.php`, `init.php`
  - Goal: Replace `chk_PosterisBanned()` with `chk_PosterIsBanned()` to avoid runtime failure.

- [x] Add a minimal routing layer for existing pretty URLs.
  - Files: `index.php` and/or `.htaccess` (if used), page entry points
  - Goal: Make links like `/hot_topics`, `/topics`, `/cat/{id}`, `/thread/{id}`, `/user/{id}`, `/new_reply/{id}` resolve to actual handlers.

- [x] Replace thread reply placeholder with real reply fetching/rendering.
  - Files: `thread.php`, `init.php`, `db/replies.sql`
  - Goal: Render replies on thread pages instead of the current placeholder message.

- [x] Fix thread fetch naming mismatch (`do_getThreadById` vs `getThreadById`).
  - Files: `init.php`, `thread.php`, `new_reply.php`
  - Goal: Thread and reply pages can load reliably.

- [x] Normalize homepage menu data shape.
  - Files: `init.php`, `index.php`, `thread.php`, `user.php`
  - Goal: Header menu renders correctly on all pages.

- [x] Align topic field names with DB schema.
  - Files: `index.php`, `db/topics.sql`
  - Goal: Replace `reply_count`/`visit_count`/`category_name` usage with schema-consistent values.

- [x] Fix unban path to call unban function.
  - Files: `do/unban.php`, `init.php`
  - Goal: Moderator unban action actually unbans.

- [x] Add missing schema files/tables used by existing code.
  - Files: `db/` (add tables for `replies`, `post_flairs`, `awards`)
  - Goal: Existing queries stop failing on missing tables.

- [x] Make `thread.php` minimally complete and valid.
  - Files: `thread.php`
  - Goal: Render thread page cleanly with valid HTML structure.

- [x] Make `new_reply.php` minimally complete.
  - Files: `new_reply.php`, `init.php`
  - Goal: Compose and submit a basic reply successfully.

## P1 - Core completion (still in scope)

- [x] Move award metadata to DB catalog.
  - Files: `db/award_catalog.sql`, `init.php`, `user.php`
  - Goal: Keep award name/description/image as database-backed source of truth.

- [x] Add threaded inbox conversations.
  - Files: `inbox.php`, `compose_message.php`, `init.php`, `db/private_messages.sql`, `htaccess`
  - Goal: Show per-user message threads and support in-thread replies.

- [x] Add minimal 404 handling for missing thread/user targets.
  - Files: page entry points and corresponding 404 handlers
  - Goal: Back `/404/thread` and `/404/user` with real responses instead of dangling redirects.

- [ ] Add a minimal admin entry page for existing moderation flows.
  - Files: `user.php`, new admin entry point if needed
  - Goal: Make the `/admin` redirect target exist before exposing admin-only actions.

- [x] Decide which menu destinations are real and wire or remove the rest.
  - Files: `init.php`, `index.php`
  - Goal: Avoid top-nav links pointing at unimplemented pages like `/folks`, `/search`, `/stuff`, `/bumps`, `/replies`, `/new_topic`.

- [x] Repair `post_Reply` and `post_Topic` SQL/bind parameter consistency.
  - Files: `init.php`
  - Goal: Posting logic is consistent and does not fail due to SQL argument mismatches.

- [x] Stabilize notifications (no advanced ticker work yet).
  - Files: `init.php`
  - Goal: Show one clear notification path first; remove early-return bugs and signature mismatch.

- [x] Implement basic `do_logentry` output.
  - Files: `init.php` (+ optional simple log file)
  - Goal: Capture actionable Notice/Warning/Error events.

- [x] Resolve page/category resolver dependency.
  - Files: `init.php`
  - Goal: Remove or implement `determine_current_page` dependency cleanly.

- [x] Fix flair consensus function naming/return shape mismatch.
  - Files: `init.php`, `thread.php`
  - Goal: Thread consensus label renders without runtime errors.

- [x] Add post edit + append workflow with DB tracking.
  - Files: `edit_post.php`, `thread.php`, `init.php`, `htaccess`, `db/topics.sql`, `db/replies.sql`, `db/post_edits.sql`
  - Goal: Allow time-limited full edits, append-only fallback, and show metadata via `is_edited` state (`0/1/2`).

## P2 - Nice-to-have polish (only after P0/P1)

- [x] Add simple `.user_awards` layout styles.
  - Files: `assets/css/layout.css`, `user.php`

- [x] Fix tag JSON search query.
  - Files: `init.php`

- [ ] Keep logout flow simple; defer logged-out notification system.
  - Files: `do/logout.php`, `init.php`

- [x] Decide whether to ship or remove commented-out thread actions.
  - Files: `thread.php`
  - Goal: Either implement or delete references to PM, watch, forget-thread, and quote-topic actions.

- [ ] Decide whether to keep the emoji conversion stub.
  - Files: `init.php`
  - Goal: Either implement `fun_EmojiTimeMachine()` or make its deferred status explicit in code/comments.

- [ ] Revisit hotness/trending logic only after routing and replies work.
  - Files: `index.php`, `init.php`
  - Goal: Replace the current placeholder idea around visits/replies with a simple real sort if still wanted.

## Explicitly deferred (out of current scope)

- Full emoji conversion system.
- Complex mention ticker/marquee behavior.
- Large moderation route rewrites.
- New social/gamification mechanics beyond existing schema.
- Cookie-based logged-out notifications.

## Best starting place

Start with: **cookie/session login typo** (`$COOKIE` vs `$_COOKIE`).

Why this first:
1. It is a small, low-risk change.
2. It fixes a real core auth flow rather than a deferred feature.
3. It gives a fast sanity check before taking on routing and reply rendering.
