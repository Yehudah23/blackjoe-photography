

const API_BASE_URL = process.env.VUE_APP_API_URL || 'http://localhost:8000';

export const API_ENDPOINTS = {

  portfolio: `${API_BASE_URL}/portfolio`,
  
  
  contact: `${API_BASE_URL}/contact`,
  
 
  admin: {
    login: `${API_BASE_URL}/admin/login`,
    logout: `${API_BASE_URL}/admin/logout`,
    user: `${API_BASE_URL}/user`,
  }
};


export const axiosConfig = {
  baseURL: API_BASE_URL,
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
  }
};

export default API_ENDPOINTS;
