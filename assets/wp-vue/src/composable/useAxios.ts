import axios from "axios";
import { has } from "lodash";

const _getBaseURL = () => {
  return `${window.location.origin}/wp-json/ai-smart-sales/v1`;
};

const _getHeaders = () => {
  if (has(window, "wpApiSettings")) {
    return {
      // @ts-ignore
      "X-WP-Nonce": window.wpApiSettings.nonce,
    };
  }
};

const axiosConfig = {
  baseURL: _getBaseURL(),
  headers: _getHeaders(),
};

export const useAxios = (_axiosConfig?: any) => {
  const userCredentials = {
    //@ts-ignore
    username: import.meta.env.VITE_APP_USER_NAME,
    //@ts-ignore
    password: import.meta.env.VITE_APP_PASSWORD,
  };

  const instance = axios.create(_axiosConfig || axiosConfig);

  // Add a response interceptor
  instance.interceptors.response.use((response) => response.data);

  const handleRequest = async (requestFn, ...args) => {
    try {
      return await requestFn(...args);
    } catch (error: unknown) {
      let errorMessage = "An error occurred.";
      if (axios.isAxiosError(error)) {
        if (error.response) {
          errorMessage =
            error.response.data.message || error.response.statusText;
        } else if (error.request) {
          errorMessage = "No response received from the server.";
        } else {
          errorMessage = error.message;
        }

        if (error.code === "ECONNABORTED") {
          errorMessage = "Request timed out.";
        }
        return {
          message: errorMessage,
          status: error.response?.status,
          error: error.response?.data?.error || null,
        };
      }
      return {
        message: errorMessage,
        status: 500,
        error: null,
      };
    }
  };

  const _handleBasicAuth = (config) => {
    return {
      ...config,
      auth: _axiosConfig ? null : userCredentials,
    };
  };

  const get = (url: string, config = {}) => {
    return handleRequest(instance.get, url, _handleBasicAuth(config));
  };

  const post = (url: string, data = {}, config = {}) => {
    return handleRequest(instance.post, url, data, _handleBasicAuth(config));
  };
  const put = (url: string, data = {}, config = {}) => {
    return handleRequest(instance.put, url, data, _handleBasicAuth(config));
  };
  const remove = (url: string, data = {}, config = {}) => {
    return handleRequest(instance.delete, url, {
      ..._handleBasicAuth(config),
      data,
    });
  };

  return {
    get,
    post,
    put,
    remove,
    handleRequest,
    axios: instance,
  };
};
