# Backend Connection Guide

## ✅ Server Status: RUNNING

The Laravel backend is now running and configured for frontend connections.

### Server Details

- **URL**: `http://localhost:8000` or `http://127.0.0.1:8000`
- **Host**: `0.0.0.0` (accessible from any network interface)
- **Port**: `8000`
- **Status**: ✅ Active (background process)

### Quick Test Commands

```bash
# Check if server is running
ps aux | grep "php artisan serve"

# Test API endpoint
curl http://localhost:8000/api/portfolio

# Test with CORS headers
curl -i http://localhost:8000/api/portfolio -H "Origin: http://localhost:8080"

# View server logs
tail -f /tmp/laravel-server.log

# Stop server
pkill -f "php artisan serve"

# Restart server
php artisan serve --host=0.0.0.0 --port=8000
```

---

## CORS Configuration ✅ FIXED

The backend now accepts requests from multiple frontend origins:

### Allowed Origins (Development)

- `http://localhost:3000` - React, Next.js
- `http://localhost:8080` - **Vue.js** (your current frontend)
- `http://localhost:5173` - Vite
- `http://127.0.0.1:3000`
- `http://127.0.0.1:8080`
- `http://127.0.0.1:5173`

### CORS Settings

- ✅ **Credentials**: Enabled (cookies/sessions work)
- ✅ **Methods**: All (GET, POST, PUT, DELETE, etc.)
- ✅ **Headers**: All allowed
- ✅ **Paths**: `/api/*` and `/sanctum/csrf-cookie`

---

## Frontend Connection Setup

### For Vue.js / Axios

```javascript
// Configure axios to send credentials (cookies)
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true, // IMPORTANT: Enable cookies/sessions
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

export default api;
```

### For Fetch API

```javascript
const response = await fetch('http://localhost:8000/api/portfolio', {
  method: 'GET',
  credentials: 'include', // IMPORTANT: Include cookies
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});
```

---

## API Endpoints

### Public Endpoints

```bash
# Get all portfolio items
GET http://localhost:8000/api/portfolio

# Response: [{ id, title, category, description, imageUrl, videoUrl }]
```

### Admin Authentication

```bash
# Login (creates session cookie)
POST http://localhost:8000/api/admin/login
Content-Type: application/json

{
  "password": "your_password"
}

# Response: { message: "Login successful", user: { role: "admin", authenticated: true } }
```

```bash
# Check authentication status
GET http://localhost:8000/api/user

# Response (if logged in): { role: "admin", authenticated: true, login_time: "..." }
# Response (if not logged in): { authenticated: false } (401)
```

```bash
# Logout
POST http://localhost:8000/api/admin/logout

# Response: { message: "Logged out successfully" }
```

### Protected Endpoints (Require Admin Login)

```bash
# Upload portfolio item (multipart/form-data)
POST http://localhost:8000/api/portfolio
Content-Type: multipart/form-data

FormData:
- file: (binary file - image or video, max 200MB)
- title: "Photo Title" (optional)
- category: "Nature" (optional)
- description: "Description text" (optional)

# Response: { id, title, category, description, imageUrl or videoUrl }
```

```bash
# Delete portfolio item
DELETE http://localhost:8000/api/portfolio/{id}

# Response: { message: "Portfolio item deleted" }
```

```bash
# Change admin password
POST http://localhost:8000/api/admin/change-password
Content-Type: application/json

{
  "current_password": "old_password",
  "new_password": "new_password"
}

# Response: { message: "Password updated" }
```

---

## Common Issues & Solutions

### Issue: "Network Error" or "CORS Error"

**Solution 1**: Verify backend is running
```bash
curl http://localhost:8000/api/portfolio
```

**Solution 2**: Check CORS headers
```bash
curl -i http://localhost:8000/api/portfolio -H "Origin: http://localhost:8080"
# Should see: Access-Control-Allow-Origin: http://localhost:8080
```

**Solution 3**: Ensure frontend sends credentials
```javascript
// Axios
withCredentials: true

// Fetch
credentials: 'include'
```

---

### Issue: "401 Unauthorized" on Protected Routes

**Cause**: Not logged in or session expired.

**Solution**: Login first, then subsequent requests will include session cookie automatically.

```javascript
// 1. Login first
await api.post('/api/admin/login', { password: 'yourpass' });

// 2. Then other requests work (cookie is sent automatically)
await api.post('/api/portfolio', formData);
```

---

### Issue: Frontend on Different Port

If your frontend runs on a port other than 8080 (e.g., 3000 or 5173), the CORS config already supports it!

Supported ports:
- 3000 (React, Next.js)
- 5173 (Vite)
- 8080 (Vue CLI)

If you need a custom port, edit `.env`:

```env
FRONTEND_URL=http://localhost:YOUR_PORT
```

Then clear cache:
```bash
php artisan config:clear
```

---

## Testing the Connection

### 1. Test Backend is Running

```bash
curl http://localhost:8000/api/portfolio
# Expected: [] (empty array) or portfolio items
```

### 2. Test CORS Headers

```bash
curl -i http://localhost:8000/api/portfolio \
  -H "Origin: http://localhost:8080"

# Look for these headers:
# Access-Control-Allow-Origin: http://localhost:8080
# Access-Control-Allow-Credentials: true
```

### 3. Test Admin Login

```bash
curl -i http://localhost:8000/api/admin/login \
  -X POST \
  -H "Content-Type: application/json" \
  -H "Origin: http://localhost:8080" \
  -d '{"password":"admin123"}'

# Look for:
# - Set-Cookie: laravel_session=...
# - {"message":"Login successful"}
```

---

## Server Management

### Start Server (if stopped)

```bash
# Background mode with logging
nohup php artisan serve --host=0.0.0.0 --port=8000 > /tmp/laravel-server.log 2>&1 &

# Or foreground mode (Ctrl+C to stop)
php artisan serve --host=0.0.0.0 --port=8000
```

### Stop Server

```bash
pkill -f "php artisan serve"
```

### Check Server Status

```bash
ps aux | grep "php artisan serve"
```

### View Logs

```bash
# Real-time logs
tail -f /tmp/laravel-server.log

# Or Laravel logs
tail -f storage/logs/laravel.log
```

---

## Frontend Code Example (Complete)

### Vue.js + Axios Setup

**File: `src/api/index.js`**

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000',
  withCredentials: true, // Enable cookies
  headers: {
    'Accept': 'application/json',
  }
});

export const adminApi = {
  // Login
  login(password) {
    return api.post('/api/admin/login', { password });
  },

  // Logout
  logout() {
    return api.post('/api/admin/logout');
  },

  // Get current user
  getCurrentUser() {
    return api.get('/api/user');
  },

  // Change password
  changePassword(currentPassword, newPassword) {
    return api.post('/api/admin/change-password', {
      current_password: currentPassword,
      new_password: newPassword,
    });
  },
};

export const portfolioApi = {
  // Get all items (public)
  getAll() {
    return api.get('/api/portfolio');
  },

  // Upload item (requires login)
  upload(formData) {
    return api.post('/api/portfolio', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },

  // Delete item (requires login)
  delete(id) {
    return api.delete(`/api/portfolio/${id}`);
  },
};

export default api;
```

**Usage in Component:**

```vue
<script>
import { adminApi, portfolioApi } from '@/api';

export default {
  data() {
    return {
      isLoggedIn: false,
      portfolioItems: [],
    };
  },

  async mounted() {
    await this.checkAuth();
    await this.loadPortfolio();
  },

  methods: {
    async checkAuth() {
      try {
        const { data } = await adminApi.getCurrentUser();
        this.isLoggedIn = data.authenticated;
      } catch (error) {
        this.isLoggedIn = false;
      }
    },

    async login(password) {
      try {
        await adminApi.login(password);
        this.isLoggedIn = true;
        this.$router.push('/admin');
      } catch (error) {
        alert('Login failed');
      }
    },

    async loadPortfolio() {
      const { data } = await portfolioApi.getAll();
      this.portfolioItems = data;
    },

    async uploadFile(file, title, category, description) {
      const formData = new FormData();
      formData.append('file', file);
      if (title) formData.append('title', title);
      if (category) formData.append('category', category);
      if (description) formData.append('description', description);

      try {
        const { data } = await portfolioApi.upload(formData);
        this.portfolioItems.push(data);
        alert('Upload successful!');
      } catch (error) {
        alert('Upload failed: ' + error.message);
      }
    },

    async deleteItem(id) {
      try {
        await portfolioApi.delete(id);
        this.portfolioItems = this.portfolioItems.filter(item => item.id !== id);
        alert('Deleted successfully');
      } catch (error) {
        alert('Delete failed');
      }
    },
  },
};
</script>
```

---

## Production Deployment

When deploying to production:

1. **Set production environment** in `.env`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   FRONTEND_URL=https://your-frontend-domain.com
   ```

2. **Use a proper web server** (Apache/Nginx, not `php artisan serve`)

3. **Enable HTTPS** for secure session cookies

4. **Update CORS** to only allow your production domain

---

## Summary

✅ **Backend is running** on `http://localhost:8000`  
✅ **CORS is configured** for `localhost:8080` (and 3000, 5173)  
✅ **Credentials are enabled** (cookies work)  
✅ **All endpoints are accessible**  

**Next Steps for Your Frontend:**

1. Set `withCredentials: true` in your axios config
2. Use base URL: `http://localhost:8000`
3. Login via `POST /api/admin/login`
4. Subsequent requests will include session cookie automatically

Your frontend should now connect successfully! 🎉
