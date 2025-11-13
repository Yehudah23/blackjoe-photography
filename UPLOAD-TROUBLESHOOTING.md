# File Upload Fix - Troubleshooting Guide

## ✅ Issues Fixed

### 1. PHP Upload Limits (CRITICAL)
**Problem**: PHP was configured to only accept 2MB files, but photos/videos are much larger.

**Fix Applied**:
```bash
# Updated /etc/php/8.3/cli/php.ini
upload_max_filesize = 200M  # Was: 2M
post_max_size = 210M        # Was: 8M
```

**Verification**:
```bash
php -i | grep -E "upload_max_filesize|post_max_size"
# Should show: 200M and 210M
```

---

### 2. Enhanced Error Handling
**Problem**: Generic errors didn't help debug upload issues.

**Fix Applied**: Updated `PortfolioController::store()` with:
- Try-catch blocks for better error messages
- File validation checks
- Detailed error responses
- Logging to `storage/logs/laravel.log`

---

### 3. Storage Permissions
**Fix Applied**:
```bash
chmod -R 775 storage/app/public
chmod -R 775 storage/logs
```

---

## Frontend Upload Configuration

### Required: Send Credentials (Cookies)

Your frontend **MUST** include session cookies with upload requests.

#### Axios Configuration

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true,  // ← CRITICAL for authenticated uploads
  headers: {
    'Accept': 'application/json',
  }
});

// Upload function
async function uploadFile(file, title, category, description) {
  // First, ensure you're logged in
  await api.post('/api/admin/login', { password: 'your_password' });
  
  // Then upload
  const formData = new FormData();
  formData.append('file', file);
  if (title) formData.append('title', title);
  if (category) formData.append('category', category);
  if (description) formData.append('description', description);
  
  try {
    const response = await api.post('/api/portfolio', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      // Track upload progress (optional)
      onUploadProgress: (progressEvent) => {
        const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
        console.log(`Upload progress: ${percent}%`);
      },
    });
    
    console.log('Upload successful:', response.data);
    return response.data;
  } catch (error) {
    if (error.response) {
      // Server responded with error
      console.error('Upload failed:', error.response.data);
      throw new Error(error.response.data.message || 'Upload failed');
    } else if (error.request) {
      // Request made but no response
      console.error('No response from server');
      throw new Error('Server not responding');
    } else {
      // Error in request setup
      console.error('Upload error:', error.message);
      throw error;
    }
  }
}
```

---

### Vue.js Component Example

```vue
<template>
  <div class="upload-form">
    <h2>Upload Portfolio Item</h2>
    
    <div v-if="!isLoggedIn" class="login-section">
      <input v-model="password" type="password" placeholder="Admin Password" />
      <button @click="login">Login</button>
    </div>
    
    <div v-else class="upload-section">
      <input type="file" @change="onFileChange" accept="image/*,video/*" />
      <input v-model="title" placeholder="Title" />
      <input v-model="category" placeholder="Category" />
      <textarea v-model="description" placeholder="Description"></textarea>
      
      <button @click="upload" :disabled="!selectedFile || uploading">
        {{ uploading ? `Uploading... ${uploadProgress}%` : 'Upload' }}
      </button>
      
      <div v-if="error" class="error">{{ error }}</div>
      <div v-if="success" class="success">Upload successful!</div>
    </div>
  </div>
</template>

<script>
import api from '@/api'; // Your axios instance

export default {
  data() {
    return {
      isLoggedIn: false,
      password: '',
      selectedFile: null,
      title: '',
      category: '',
      description: '',
      uploading: false,
      uploadProgress: 0,
      error: null,
      success: false,
    };
  },
  
  async mounted() {
    // Check if already logged in
    try {
      await api.get('/api/user');
      this.isLoggedIn = true;
    } catch {
      this.isLoggedIn = false;
    }
  },
  
  methods: {
    async login() {
      try {
        await api.post('/api/admin/login', { password: this.password });
        this.isLoggedIn = true;
        this.error = null;
      } catch (error) {
        this.error = 'Login failed: ' + (error.response?.data?.message || error.message);
      }
    },
    
    onFileChange(event) {
      this.selectedFile = event.target.files[0];
      this.error = null;
      this.success = false;
      
      // Validate file size (200MB = 209715200 bytes)
      if (this.selectedFile.size > 209715200) {
        this.error = 'File is too large. Maximum size is 200MB.';
        this.selectedFile = null;
      }
    },
    
    async upload() {
      if (!this.selectedFile) {
        this.error = 'Please select a file';
        return;
      }
      
      this.uploading = true;
      this.uploadProgress = 0;
      this.error = null;
      this.success = false;
      
      const formData = new FormData();
      formData.append('file', this.selectedFile);
      if (this.title) formData.append('title', this.title);
      if (this.category) formData.append('category', this.category);
      if (this.description) formData.append('description', this.description);
      
      try {
        const response = await api.post('/api/portfolio', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
          onUploadProgress: (progressEvent) => {
            this.uploadProgress = Math.round(
              (progressEvent.loaded * 100) / progressEvent.total
            );
          },
        });
        
        this.success = true;
        this.error = null;
        
        // Reset form
        this.selectedFile = null;
        this.title = '';
        this.category = '';
        this.description = '';
        this.uploadProgress = 0;
        
        // Emit event or update portfolio list
        this.$emit('upload-complete', response.data);
        
        // Show success for 3 seconds
        setTimeout(() => {
          this.success = false;
        }, 3000);
      } catch (error) {
        console.error('Upload error:', error);
        
        if (error.response) {
          // Server error response
          this.error = error.response.data.message || 'Upload failed';
          
          // Handle specific errors
          if (error.response.status === 401) {
            this.error = 'Session expired. Please login again.';
            this.isLoggedIn = false;
          } else if (error.response.status === 422) {
            // Validation errors
            const errors = error.response.data.errors;
            this.error = Object.values(errors).flat().join(', ');
          }
        } else if (error.request) {
          this.error = 'Server not responding. Please check backend is running.';
        } else {
          this.error = error.message;
        }
      } finally {
        this.uploading = false;
      }
    },
  },
};
</script>

<style scoped>
.upload-form {
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
}

input, textarea, button {
  display: block;
  width: 100%;
  margin: 10px 0;
  padding: 10px;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error {
  color: red;
  padding: 10px;
  background: #fee;
  border-radius: 4px;
}

.success {
  color: green;
  padding: 10px;
  background: #efe;
  border-radius: 4px;
}
</style>
```

---

## Testing Upload from Command Line

```bash
# 1. Login first (saves cookie)
curl -c /tmp/cookies.txt \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{"password":"admin123"}' \
  http://localhost:8000/api/admin/login

# 2. Upload a file
curl -b /tmp/cookies.txt \
  -X POST \
  -F "file=@path/to/your/image.jpg" \
  -F "title=Test Image" \
  -F "category=Photography" \
  -F "description=This is a test upload" \
  http://localhost:8000/api/portfolio

# 3. Or use the test script
./test-upload.sh path/to/image.jpg
```

---

## Common Upload Errors & Solutions

### Error: "No file uploaded" (FILE_MISSING)

**Cause**: File not included in request or wrong field name.

**Solution**: 
- Ensure FormData uses field name `file`: `formData.append('file', fileObject)`
- Check file input: `<input type="file" @change="onFileChange" />`

---

### Error: "Validation failed" (422)

**Causes**:
1. File too large (>200MB)
2. Invalid file type
3. No file selected

**Allowed Types**:
- Images: jpg, jpeg, png, gif, webp
- Videos: mp4, mov, avi, wmv, flv, webm

**Solution**: Check `error.response.data.errors` for specific validation messages.

---

### Error: "Unauthorized" (401)

**Cause**: Not logged in or session expired.

**Solution**:
1. Ensure you called `POST /api/admin/login` first
2. Ensure `withCredentials: true` in axios config
3. Check cookies are being sent in request headers

---

### Error: "The file failed to upload"

**Causes**:
1. PHP upload limits too small
2. Storage directory not writable
3. Network timeout for large files

**Solutions**:

1. Check PHP limits:
```bash
php -i | grep -E "upload_max_filesize|post_max_size|max_execution_time"
```

2. Check storage permissions:
```bash
ls -la storage/app/public/portfolio
chmod -R 775 storage/app/public
```

3. Increase timeouts in frontend:
```javascript
axios.create({
  timeout: 300000, // 5 minutes for large uploads
});
```

---

### Error: "Address already in use" (Server won't start)

**Cause**: Port 8000 is already in use.

**Solution**:
```bash
# Kill existing process
sudo fuser -k 8000/tcp

# Or find and kill manually
ps aux | grep "php artisan serve"
kill <PID>

# Restart server
php artisan serve --host=0.0.0.0 --port=8000
```

---

## Debugging Tips

### 1. Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

Look for:
- Upload errors
- Validation failures
- File storage issues

---

### 2. Check Server Logs

```bash
tail -f /tmp/laravel-server.log
```

---

### 3. Test with Browser DevTools

1. Open Network tab in browser DevTools
2. Attempt upload
3. Check request:
   - **Headers**: Should include `Cookie: laravel_session=...`
   - **Payload**: Should show FormData with file
   - **Response**: Check error messages

---

### 4. Verify CORS Headers

```bash
curl -i http://localhost:8000/api/portfolio \
  -H "Origin: http://localhost:8080"

# Should see:
# Access-Control-Allow-Origin: http://localhost:8080
# Access-Control-Allow-Credentials: true
```

---

## Server Configuration Summary

### PHP Settings (Updated)
```ini
upload_max_filesize = 200M
post_max_size = 210M
max_file_uploads = 20
max_execution_time = 300
```

### Storage Structure
```
storage/app/public/portfolio/  ← Uploaded files stored here
public/storage/ → symlink to storage/app/public
```

### CORS (config/cors.php)
```php
'allowed_origins' => [
    'http://localhost:3000',
    'http://localhost:8080',  ← Your Vue.js app
    'http://localhost:5173',
],
'supports_credentials' => true,
```

---

## Quick Checklist

Before troubleshooting, verify:

- [ ] Backend server is running (`ps aux | grep "php artisan serve"`)
- [ ] PHP upload limits are 200M (`php -i | grep upload_max_filesize`)
- [ ] Storage is writable (`ls -la storage/app/public`)
- [ ] You're logged in (`curl http://localhost:8000/api/user` with cookies)
- [ ] Frontend sends credentials (`withCredentials: true` or `credentials: 'include'`)
- [ ] File is under 200MB
- [ ] File type is supported (jpg, png, mp4, etc.)
- [ ] CORS allows your origin

---

## Current Server Status

```bash
# Check if server is running
ps aux | grep "php artisan serve"

# Test API
curl http://localhost:8000/api/portfolio

# Check PHP config
php -i | grep -E "upload_max_filesize|post_max_size"

# Should show:
# upload_max_filesize => 200M
# post_max_size => 210M
```

---

## Need Help?

1. Check `storage/logs/laravel.log` for detailed errors
2. Run `./test-upload.sh test-image.jpg` to test backend
3. Check browser DevTools Network tab for request/response details
4. Ensure `withCredentials: true` in axios configuration

✅ **Backend is configured and ready for uploads!**
