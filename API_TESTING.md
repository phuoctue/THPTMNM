# API Testing Guide

## 1. Setup
- Import `WebBanHang.sql` into MySQL.
- Copy `.env.example` to `.env`.
- Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.
- Set a strong `JWT_SECRET`.
- Point your local domain to the project root.

## 2. Postman environment
- `base_url` = `http://localhost:8080`
- `token` = JWT returned from `/api/login`

## 3. Auth flow
1. `POST {{base_url}}/api/register`
2. `POST {{base_url}}/api/login`
3. Save `data.token` to `token`
4. Use header `Authorization: Bearer {{token}}`

## 4. Core requests
- `GET {{base_url}}/api/products`
- `GET {{base_url}}/api/products/1`
- `GET {{base_url}}/api/products/search?q=iphone`
- `GET {{base_url}}/api/products/filter?category_id=1&min_price=1000000&max_price=30000000`
- `GET {{base_url}}/api/products/sort?direction=asc`
- `POST {{base_url}}/api/products` with `form-data` and `image`
- `GET {{base_url}}/api/categories`
- `POST {{base_url}}/api/cart/add`
- `POST {{base_url}}/api/orders`
- `POST {{base_url}}/api/payments`

## 5. Suggested Postman tests
- 401 when token is missing on `/api/me`
- 403 when customer calls admin-only endpoints
- 422 when validation fails
- 409 when deleting a category that still has products
- 201 when create product/order/payment succeeds

## 6. Notes
- `POST /api/login` returns JSON with `token`, `token_type`, `expires_in`, `expires_at`, and `user`.
- Frontend should store the token in `localStorage` and send it as `Bearer` in the `Authorization` header.
