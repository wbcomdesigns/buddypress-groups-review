# BuddyPress Group Reviews - REST API Integration

## Overview

The BuddyPress Group Reviews plugin extends the native **BuddyPress REST API** (`/buddypress/v1/`) with review data and endpoints. This provides seamless integration with BuddyPress groups and makes reviews easily discoverable.

**Base URL:** `/wp-json/buddypress/v1/`
**Version:** Extends BuddyPress v1 API
**Namespace:** `buddypress/v1`

---

## Why BuddyPress REST API?

✅ **Discoverable** - Automatic discovery through BuddyPress API
✅ **Consistent** - Follows BuddyPress conventions and patterns
✅ **Integrated** - Review data appears in group responses
✅ **Standard** - Uses established BP authentication methods
✅ **Future-proof** - Maintained alongside BuddyPress core

---

## Integration Features

### 1. Review Data in Group Responses

When fetching groups via BuddyPress API, review statistics are automatically included:

**Example Request:**
```bash
GET /wp-json/buddypress/v1/groups/123
```

**Response includes:**
```json
{
  "id": 123,
  "name": "WordPress Developers",
  "description": { "raw": "...", "rendered": "..." },
  "link": "https://example.com/groups/wordpress-developers/",
  "status": "public",
  "reviews": {
    "average_rating": 4.7,
    "total_reviews": 23,
    "ratings": {
      "Quality": 4.8,
      "Engagement": 4.5,
      "Helpfulness": 4.9
    },
    "can_review": true
  },
  "_links": { ... }
}
```

**New Fields Added:**
- `reviews.average_rating` - Overall average rating (0-5)
- `reviews.total_reviews` - Count of published reviews
- `reviews.ratings` - Average per criteria
- `reviews.can_review` - Whether current user can submit a review

---

## Review Endpoints

### 1. List Group Reviews

**Endpoint:** `GET /wp-json/buddypress/v1/groups/{group_id}/reviews`

**Authentication:** None (public reviews)

**Parameters:**
| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| group_id | integer | - | BuddyPress group ID (required) |
| page | integer | 1 | Page number for pagination |
| per_page | integer | 10 | Reviews per page (1-100) |

**Example Request:**
```bash
curl https://example.com/wp-json/buddypress/v1/groups/123/reviews?per_page=5
```

**Example Response:**
```json
[
  {
    "id": 456,
    "group_id": 123,
    "subject": "Excellent community",
    "description": "Very helpful and active members.",
    "status": "publish",
    "author": {
      "id": 789,
      "name": "John Doe"
    },
    "ratings": {
      "Quality": 5,
      "Engagement": 4,
      "Helpfulness": 5
    },
    "created": "2025-01-15 10:30:00",
    "modified": "2025-01-15 10:30:00"
  }
]
```

**Response Headers:**
- `X-WP-Total`: Total number of reviews
- `X-WP-TotalPages`: Total pages available

---

### 2. Create Group Review

**Endpoint:** `POST /wp-json/buddypress/v1/groups/{group_id}/reviews`

**Authentication:** Required (logged-in user)

**Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| group_id | integer | Yes | BuddyPress group ID |
| subject | string | Yes | Review title/subject |
| description | string | Yes | Review content |
| ratings | object | No | Star ratings by criteria |

**Example Request:**
```bash
curl -X POST https://example.com/wp-json/buddypress/v1/groups/123/reviews \
  -u "username:app_password" \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Great group!",
    "description": "Very helpful community with active discussions.",
    "ratings": {
      "Quality": 5,
      "Engagement": 4,
      "Helpfulness": 5
    }
  }'
```

**Success Response (200):**
```json
{
  "id": 457,
  "review": {
    "id": 457,
    "group_id": 123,
    "subject": "Great group!",
    "description": "Very helpful community with active discussions.",
    "status": "publish",
    "author": {
      "id": 789,
      "name": "John Doe"
    },
    "ratings": {
      "Quality": 5,
      "Engagement": 4,
      "Helpfulness": 5
    },
    "created": "2025-01-15 11:00:00",
    "modified": "2025-01-15 11:00:00"
  },
  "status": "publish",
  "message": "Review published successfully."
}
```

**Error Responses:**

401 - Not Authenticated:
```json
{
  "code": "rest_not_logged_in",
  "message": "You must be logged in to create a review."
}
```

403 - Cannot Review Own Group:
```json
{
  "code": "rest_cannot_review_own_group",
  "message": "You cannot review your own group."
}
```

403 - Already Reviewed:
```json
{
  "code": "rest_review_already_exists",
  "message": "You have already submitted a review for this group."
}
```

---

### 3. Get Single Review

**Endpoint:** `GET /wp-json/buddypress/v1/groups/{group_id}/reviews/{review_id}`

**Authentication:** None (if review is published)

**Example Request:**
```bash
curl https://example.com/wp-json/buddypress/v1/groups/123/reviews/456
```

**Example Response:**
```json
{
  "id": 456,
  "group_id": 123,
  "subject": "Excellent community",
  "description": "Very helpful and active members.",
  "status": "publish",
  "author": {
    "id": 789,
    "name": "John Doe"
  },
  "ratings": {
    "Quality": 5,
    "Engagement": 4,
    "Helpfulness": 5
  },
  "created": "2025-01-15 10:30:00",
  "modified": "2025-01-15 10:30:00"
}
```

---

### 4. Update Review

**Endpoint:** `PUT /wp-json/buddypress/v1/groups/{group_id}/reviews/{review_id}`

**Authentication:** Required (review author or admin)

**Example Request:**
```bash
curl -X PUT https://example.com/wp-json/buddypress/v1/groups/123/reviews/456 \
  -u "username:app_password" \
  -H "Content-Type: application/json" \
  -d '{
    "subject": "Updated: Excellent community",
    "ratings": {
      "Quality": 5,
      "Engagement": 5,
      "Helpfulness": 5
    }
  }'
```

---

### 5. Delete Review

**Endpoint:** `DELETE /wp-json/buddypress/v1/groups/{group_id}/reviews/{review_id}`

**Authentication:** Required (review author or admin)

**Example Request:**
```bash
curl -X DELETE https://example.com/wp-json/buddypress/v1/groups/123/reviews/456 \
  -u "username:app_password"
```

**Success Response:**
```json
{
  "deleted": true,
  "message": "Review deleted successfully."
}
```

---

## Authentication Methods

### 1. Application Passwords (Recommended)
```bash
curl https://example.com/wp-json/buddypress/v1/groups/123/reviews \
  -u "username:APPLICATION_PASSWORD"
```

### 2. WordPress Cookies (Browser/AJAX)
```javascript
fetch('/wp-json/buddypress/v1/groups/123/reviews', {
  method: 'GET',
  credentials: 'same-origin',
  headers: {
    'X-WP-Nonce': wpApiSettings.nonce
  }
})
```

### 3. OAuth (via Plugin)
Use WordPress OAuth plugins compatible with BuddyPress:
- WP OAuth Server
- JWT Authentication for WP REST API

---

## Code Examples

### JavaScript - Fetch Group with Reviews

```javascript
async function getGroupWithReviews(groupId) {
  // Get group data (includes review stats)
  const groupResponse = await fetch(`/wp-json/buddypress/v1/groups/${groupId}`);
  const group = await groupResponse.json();

  console.log('Group:', group.name);
  console.log('Average Rating:', group.reviews.average_rating);
  console.log('Total Reviews:', group.reviews.total_reviews);
  console.log('Can Review:', group.reviews.can_review);

  // Get detailed reviews
  const reviewsResponse = await fetch(`/wp-json/buddypress/v1/groups/${groupId}/reviews`);
  const reviews = await reviewsResponse.json();

  return { group, reviews };
}

// Usage
const data = await getGroupWithReviews(123);
console.log(data);
```

### JavaScript - Create Review

```javascript
async function submitReview(groupId, reviewData) {
  const response = await fetch(`/wp-json/buddypress/v1/groups/${groupId}/reviews`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-WP-Nonce': wpApiSettings.nonce
    },
    credentials: 'same-origin',
    body: JSON.stringify(reviewData)
  });

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message);
  }

  return await response.json();
}

// Usage
try {
  const result = await submitReview(123, {
    subject: 'Amazing group!',
    description: 'Best community I\'ve joined.',
    ratings: {
      Quality: 5,
      Engagement: 5,
      Helpfulness: 5
    }
  });

  console.log('Review created:', result.id);
  console.log('Status:', result.message);
} catch (error) {
  console.error('Error:', error.message);
}
```

### PHP - Get Groups with High Ratings

```php
<?php
// Get all groups via BP REST API
$response = wp_remote_get( home_url( '/wp-json/buddypress/v1/groups?per_page=100' ) );
$groups = json_decode( wp_remote_retrieve_body( $response ), true );

// Filter groups with average rating >= 4.5
$high_rated_groups = array_filter( $groups, function( $group ) {
    return isset( $group['reviews']['average_rating'] )
        && $group['reviews']['average_rating'] >= 4.5;
} );

// Sort by rating (highest first)
usort( $high_rated_groups, function( $a, $b ) {
    return $b['reviews']['average_rating'] <=> $a['reviews']['average_rating'];
} );

foreach ( $high_rated_groups as $group ) {
    echo sprintf(
        '%s - ⭐ %.1f (%d reviews)<br>',
        $group['name'],
        $group['reviews']['average_rating'],
        $group['reviews']['total_reviews']
    );
}
```

### Python - Analyze Group Reviews

```python
import requests
from statistics import mean

base_url = 'https://example.com/wp-json/buddypress/v1'

# Get all groups
groups_response = requests.get(f'{base_url}/groups?per_page=100')
groups = groups_response.json()

for group in groups:
    if group['reviews']['total_reviews'] > 0:
        # Get detailed reviews
        reviews_response = requests.get(f"{base_url}/groups/{group['id']}/reviews?per_page=100")
        reviews = reviews_response.json()

        # Calculate sentiment
        quality_scores = [r['ratings'].get('Quality', 0) for r in reviews]
        avg_quality = mean(quality_scores) if quality_scores else 0

        print(f"{group['name']}: {avg_quality:.2f} quality score")
```

---

## Discovery

### 1. BuddyPress API Index

The reviews integration is discoverable through the BuddyPress API:

```bash
curl https://example.com/wp-json/buddypress/v1/
```

Look for `groups` routes and the extended schema.

### 2. OPTIONS Request

Get endpoint information:

```bash
curl -X OPTIONS https://example.com/wp-json/buddypress/v1/groups/123/reviews
```

Returns schema and available methods.

---

## Permissions & Capabilities

### Who Can Review?
- ✅ Logged-in members
- ❌ Group admins (cannot review own group)
- ❌ Users who already reviewed (if multiple reviews disabled)
- ❌ Members of excluded groups

### Review Permissions:
- **Create**: `is_user_logged_in()`
- **Read**: Public (anyone)
- **Update**: `edit_review` capability (author or admin)
- **Delete**: `delete_review` capability (author or admin)

---

## Response Codes

| Code | Meaning | Example |
|------|---------|---------|
| 200 | Success | Review retrieved |
| 201 | Created | Review published |
| 400 | Bad Request | Invalid parameters |
| 401 | Unauthorized | Not logged in |
| 403 | Forbidden | Cannot review own group |
| 404 | Not Found | Invalid group/review ID |
| 500 | Server Error | Internal error |

---

## Best Practices

### 1. Use Pagination
```javascript
// Get all reviews (paginated)
async function getAllGroupReviews(groupId) {
  let page = 1;
  let allReviews = [];
  let hasMore = true;

  while (hasMore) {
    const response = await fetch(
      `/wp-json/buddypress/v1/groups/${groupId}/reviews?page=${page}&per_page=20`
    );

    const reviews = await response.json();
    allReviews = allReviews.concat(reviews);

    const totalPages = parseInt(response.headers.get('X-WP-TotalPages'));
    hasMore = page < totalPages;
    page++;
  }

  return allReviews;
}
```

### 2. Cache Group Data
```javascript
// Cache group data including reviews
const groupCache = new Map();

async function getCachedGroup(groupId, maxAge = 60000) {
  const cached = groupCache.get(groupId);

  if (cached && Date.now() - cached.timestamp < maxAge) {
    return cached.data;
  }

  const response = await fetch(`/wp-json/buddypress/v1/groups/${groupId}`);
  const data = await response.json();

  groupCache.set(groupId, {
    data,
    timestamp: Date.now()
  });

  return data;
}
```

### 3. Handle Errors Gracefully
```javascript
async function safelySubmitReview(groupId, reviewData) {
  try {
    const response = await fetch(`/wp-json/buddypress/v1/groups/${groupId}/reviews`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
      },
      body: JSON.stringify(reviewData)
    });

    if (!response.ok) {
      const error = await response.json();

      // Handle specific errors
      if (error.code === 'rest_cannot_review_own_group') {
        alert('You cannot review your own group.');
      } else if (error.code === 'rest_review_already_exists') {
        alert('You have already reviewed this group.');
      } else {
        alert(`Error: ${error.message}`);
      }

      return null;
    }

    return await response.json();
  } catch (error) {
    console.error('Network error:', error);
    alert('Failed to submit review. Please try again.');
    return null;
  }
}
```

---

## Testing

### 1. Test with WP-CLI
```bash
# Get groups
wp eval "echo json_encode(BP_Groups_Group::get(array('per_page' => 5)));"

# Get reviews via REST
curl http://yoursite.local/wp-json/buddypress/v1/groups/123/reviews
```

### 2. Test with Postman
- Import BuddyPress API collection
- Add review endpoints to collection
- Test authentication methods
- Verify response schemas

### 3. Test Discovery
```bash
# Check if review fields appear in group schema
curl http://yoursite.local/wp-json/buddypress/v1/groups | jq '.[0].reviews'
```

---

## Limitations

1. **Rate Limiting**: No built-in rate limiting (consider server-level or plugin)
2. **File Uploads**: No image/file upload support in reviews
3. **Batch Operations**: No bulk create/update/delete endpoints
4. **Webhooks**: No webhook notifications for new reviews

---

## Support

- **BuddyPress REST API Docs**: https://developer.buddypress.org/bp-rest-api/
- **Plugin Issues**: https://github.com/wbcomdesigns/buddypress-groups-review/issues
- **Support**: https://wbcomdesigns.com/contact/

---

## Version Compatibility

- **BuddyPress**: 12.0.0+
- **WordPress**: 5.0+
- **PHP**: 7.4+

---

**License:** GPL-2.0+
