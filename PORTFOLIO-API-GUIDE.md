# Portfolio API Guide

This guide explains how to use the Portfolio API endpoints for managing images and videos.

## Authentication

All write operations (create, update, delete) require admin authentication. Include the authentication token in your requests.

## API Endpoints

### 1. List All Portfolio Items
**GET** `/portfolio`

Returns all portfolio items.

**Response:**
```json
[
  {
    "id": "1",
    "title": "Sample Image",
    "category": "Photography",
    "description": "A beautiful photo",
    "imageUrl": "http://localhost/storage/portfolio/image.jpg",
    "videoUrl": null
  }
]
```

---

### 2. Upload New Portfolio Item
**POST** `/portfolio` (Requires Authentication)

Upload a new image or video with metadata.

**Request (multipart/form-data):**
- `file` (required): Image or video file (max 200MB)
  - Images: jpg, jpeg, png, gif, webp
  - Videos: mp4, mov, avi, wmv, flv, webm
- `title` (optional): Title of the item
- `category` (optional): Category name (default: "Other")
- `description` (optional): Description text

**Example using cURL:**
```bash
curl -X POST http://localhost:8000/portfolio \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/image.jpg" \
  -F "title=My Photo" \
  -F "category=Nature" \
  -F "description=A beautiful landscape"
```

**Response (201):**
```json
{
  "id": "1",
  "title": "My Photo",
  "category": "Nature",
  "description": "A beautiful landscape",
  "imageUrl": "http://localhost/storage/portfolio/image.jpg",
  "videoUrl": null
}
```

---

### 3. Update Portfolio Item
**PUT/PATCH** `/portfolio/{id}` (Requires Authentication)

Update an existing portfolio item. You can update just the metadata, just the file, or both.

**Request (multipart/form-data):**
- `file` (optional): New image or video file to replace the existing one
- `title` (optional): New title
- `category` (optional): New category
- `description` (optional): New description

**Example - Update only metadata:**
```bash
curl -X PUT http://localhost:8000/portfolio/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "title=Updated Title" \
  -F "category=Updated Category" \
  -F "description=Updated description"
```

**Example - Update only the file:**
```bash
curl -X PUT http://localhost:8000/portfolio/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/new-image.jpg"
```

**Example - Update both file and metadata:**
```bash
curl -X PUT http://localhost:8000/portfolio/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@/path/to/new-image.jpg" \
  -F "title=Completely Updated" \
  -F "category=New Category"
```

**Response (200):**
```json
{
  "id": "1",
  "title": "Updated Title",
  "category": "Updated Category",
  "description": "Updated description",
  "imageUrl": "http://localhost/storage/portfolio/new-image.jpg",
  "videoUrl": null
}
```

**Note:** When you upload a new file, the old file is automatically deleted from storage.

---

### 4. Delete Portfolio Item
**DELETE** `/portfolio/{id}` (Requires Authentication)

Delete a portfolio item and its associated file from storage.

**Example:**
```bash
curl -X DELETE http://localhost:8000/portfolio/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200):**
```json
{
  "message": "Portfolio item deleted"
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "Validation failed",
  "errors": {
    "file": ["The file must be a file of type: jpg, jpeg, png, gif, webp, mp4, mov, avi, wmv, flv, webm."]
  }
}
```

### Not Found (404)
```json
{
  "message": "Portfolio item not found",
  "error": "NOT_FOUND"
}
```

### Server Error (500)
```json
{
  "message": "Update failed: error details",
  "error": "UPDATE_FAILED"
}
```

---

## JavaScript/Fetch Example

### Update Portfolio Item with JavaScript:
```javascript
// Update metadata only
async function updatePortfolioMetadata(id, data) {
  const formData = new FormData();
  if (data.title) formData.append('title', data.title);
  if (data.category) formData.append('category', data.category);
  if (data.description) formData.append('description', data.description);
  
  const response = await fetch(`/portfolio/${id}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${yourToken}`
    },
    body: formData
  });
  
  return await response.json();
}

// Update with new file
async function updatePortfolioFile(id, file, metadata = {}) {
  const formData = new FormData();
  formData.append('file', file);
  if (metadata.title) formData.append('title', metadata.title);
  if (metadata.category) formData.append('category', metadata.category);
  if (metadata.description) formData.append('description', metadata.description);
  
  const response = await fetch(`/portfolio/${id}`, {
    method: 'PUT',
    headers: {
      'Authorization': `Bearer ${yourToken}`
    },
    body: formData
  });
  
  return await response.json();
}

// Delete portfolio item
async function deletePortfolio(id) {
  const response = await fetch(`/portfolio/${id}`, {
    method: 'DELETE',
    headers: {
      'Authorization': `Bearer ${yourToken}`
    }
  });
  
  return await response.json();
}
```

---

## Testing with test-upload.sh

You can create a test script for updating:

```bash
#!/bin/bash

# Test updating a portfolio item
PORTFOLIO_ID="1"
TOKEN="your-admin-token"

# Update metadata only
curl -X PUT "http://localhost:8000/portfolio/$PORTFOLIO_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -F "title=Updated Title" \
  -F "category=Updated Category"

# Update with new file
curl -X PUT "http://localhost:8000/portfolio/$PORTFOLIO_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/path/to/new-image.jpg" \
  -F "title=New Image"
```

---

## Important Notes

1. **File Replacement**: When updating with a new file, the old file is automatically deleted from storage
2. **Partial Updates**: You can update only specific fields - any fields not included in the request will remain unchanged
3. **File Size Limit**: Maximum file size is 200MB (204800KB)
4. **Authentication**: All write operations require admin authentication
5. **Error Logging**: All errors are logged to the Laravel log file for debugging
