import axios from 'axios';

const factusAxios = axios.create({
  baseURL: 'https://api-sandbox.factus.com.co/',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
    // OJO: No poner 'X-Requested-With': 'XMLHttpRequest'
  }
});

export default factusAxios;