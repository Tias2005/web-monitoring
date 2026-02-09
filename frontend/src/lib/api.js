import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    "ngrok-skip-browser-warning": "true",
    "Accept": "application/json"
  }
});

export default api;
