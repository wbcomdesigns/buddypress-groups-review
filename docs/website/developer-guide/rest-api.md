# REST API

The plugin exposes group reviews under the BuddyPress REST namespace `buddypress/v1`. This backs mobile apps and headless front ends.

## Endpoints

### Collection

`/wp-json/buddypress/v1/groups/{group_id}/reviews`

| Method | Auth | Purpose |
|---|---|---|
| GET | Public for public groups; members or `bp_moderate` for private and hidden groups (since 3.9.1) | List a group's reviews. Supports `page` (default 1; `0` is treated as `1`) and `per_page` (default 10; values above 100 are capped at 100). |
| POST | Logged-in user, must be a member of the group | Create a review. |

Create parameters: `subject` (required), `description` (required), `ratings` (optional array of per-criterion scores).

### Single review

`/wp-json/buddypress/v1/groups/{group_id}/reviews/{review_id}`

| Method | Auth | Purpose |
|---|---|---|
| GET | Public for public groups; members or `bp_moderate` for private and hidden groups (since 3.9.1) | Fetch one review. |
| PUT / PATCH | Permission check (author or capable user) | Update `subject`, `description`, or `ratings`. |
| DELETE | Permission check (author or capable user) | Delete the review. |

## Notes

- The collection GET and single GET are public for public groups. For private and hidden groups they need group membership or `bp_moderate` (since 3.9.1).
- A group excluded from reviews returns `403` with the code `rest_group_excluded` on both listing and creating (since 3.9.2).
- Creating a review requires an authenticated user who is a member of the target group; non-members are rejected.
- `group_id` and `review_id` are validated as numeric and sanitized with `absint`.
- Both routes expose a schema via the endpoint's `schema` callback.

## Example

```bash
# List reviews for group 42
curl https://example.com/wp-json/buddypress/v1/groups/42/reviews?per_page=5

# Create a review (authenticated request)
curl -X POST https://example.com/wp-json/buddypress/v1/groups/42/reviews \
  -H "Content-Type: application/json" \
  -d '{"subject":"Great group","description":"Very active.","ratings":[5,4,5]}'
```
