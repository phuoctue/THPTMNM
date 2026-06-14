(function (window) {
    "use strict";

    var TOKEN_KEY = "my_store_token";
    var ALT_TOKEN_KEY = "auth_token";
    var USER_KEY = "my_store_user";
    var API_BASE_URL = "/api";

    function getToken() {
        return localStorage.getItem(TOKEN_KEY) || localStorage.getItem(ALT_TOKEN_KEY) || "";
    }

    function base64UrlDecode(input) {
        var output = String(input || "").replace(/-/g, "+").replace(/_/g, "/");
        while (output.length % 4) {
            output += "=";
        }

        try {
            return decodeURIComponent(Array.prototype.map.call(atob(output), function (char) {
                return "%" + ("00" + char.charCodeAt(0).toString(16)).slice(-2);
            }).join(""));
        } catch (error) {
            return "";
        }
    }

    function decodeJwt(token) {
        if (!token || token.split(".").length !== 3) {
            return null;
        }

        try {
            var payload = token.split(".")[1];
            return JSON.parse(base64UrlDecode(payload));
        } catch (error) {
            return null;
        }
    }

    function isTokenExpired(token) {
        var payload = decodeJwt(token || getToken());
        if (!payload || !payload.exp) {
            return false;
        }

        return Math.floor(Date.now() / 1000) >= Number(payload.exp);
    }

    function hasValidToken() {
        var token = getToken();
        return !!token && !isTokenExpired(token);
    }

    function setToken(token, user) {
        if (!token) {
            return;
        }

        localStorage.setItem(TOKEN_KEY, token);
        localStorage.removeItem(ALT_TOKEN_KEY);

        if (user) {
            localStorage.setItem(USER_KEY, JSON.stringify(user));
        }
    }

    function clearToken() {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(ALT_TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
    }

    function normalizeEndpoint(endpoint) {
        if (!endpoint) {
            return API_BASE_URL;
        }

        if (endpoint.indexOf("/api") === 0) {
            return endpoint;
        }

        return API_BASE_URL + (endpoint.charAt(0) === "/" ? endpoint : "/" + endpoint);
    }

    function shouldAutoRedirectOnUnauthorized(endpoint) {
        return !/^\/api\/auth\/(login|register|forgotPassword|resetPassword)$/i.test(endpoint);
    }

    async function request(endpoint, options) {
        options = options || {};

        var headers = new Headers(options.headers || {});
        var token = getToken();
        var isFormData = typeof FormData !== "undefined" && options.body instanceof FormData;

        if (!headers.has("Accept")) {
            headers.set("Accept", "application/json");
        }

        if (token) {
            headers.set("Authorization", "Bearer " + token);
        }

        if (!isFormData && options.body && !headers.has("Content-Type")) {
            headers.set("Content-Type", "application/json");
        }

        if (!headers.has("X-Requested-With")) {
            headers.set("X-Requested-With", "XMLHttpRequest");
        }

        var response = await fetch(normalizeEndpoint(endpoint), {
            ...options,
            headers: headers,
        });

        var raw = await response.text();
        var payload = {};

        if (raw) {
            try {
                payload = JSON.parse(raw);
            } catch (error) {
                payload = { message: raw };
            }
        }

        if (!response.ok) {
            var apiError = new Error((payload && payload.message) ? payload.message : "Request failed");
            apiError.status = response.status;
            apiError.payload = payload;

            if (response.status === 401 && shouldAutoRedirectOnUnauthorized(endpoint)) {
                clearToken();
                window.dispatchEvent(new CustomEvent("auth:unauthorized", {
                    detail: { endpoint: endpoint, payload: payload },
                }));
                window.location.href = "/auth/login";
            }

            throw apiError;
        }

        return payload;
    }

    window.ApiClient = {
        getToken: getToken,
        hasValidToken: hasValidToken,
        isTokenExpired: isTokenExpired,
        setToken: setToken,
        clearToken: clearToken,
        request: request,
        get: function (endpoint, options) {
            return request(endpoint, { ...(options || {}), method: "GET" });
        },
        post: function (endpoint, body, options) {
            var requestOptions = { ...(options || {}), method: "POST" };
            if (body !== undefined) {
                requestOptions.body = body instanceof FormData ? body : JSON.stringify(body);
            }
            return request(endpoint, requestOptions);
        },
        put: function (endpoint, body, options) {
            var requestOptions = { ...(options || {}), method: "PUT" };
            if (body !== undefined) {
                requestOptions.body = body instanceof FormData ? body : JSON.stringify(body);
            }
            return request(endpoint, requestOptions);
        },
        delete: function (endpoint, options) {
            return request(endpoint, { ...(options || {}), method: "DELETE" });
        },
    };
})(window);
