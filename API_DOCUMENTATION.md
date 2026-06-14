# API Documentation

This document outlines the RESTful API endpoints for the WebBanHang project.

**Base URL:** `/api`
**Content-Type:** `application/json`
**Authentication:** `Bearer <token>` (in Authorization header)

---

## 1. Authentication (`/api/auth`)

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| POST | `/auth/register` | Register new user | No |
| POST | `/auth/login` | Login and get JWT | No |
| POST | `/auth/logout` | Logout user | Yes |
| GET | `/auth/me` | Get current user info | Yes |

---

## 2. Products (`/api/products`)

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| GET | `/products` | List products (with pagination) | No |
| GET | `/products/{id}` | Get product details | No |
| POST | `/products` | Add new product (Admin) | Yes (Admin) |
| PUT | `/products/{id}` | Update product (Admin) | Yes (Admin) |
| DELETE | `/products/{id}` | Delete product (Admin) | Yes (Admin) |

---

## 3. Categories (`/api/categories`)

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| GET | `/categories` | List categories | No |
| POST | `/categories` | Add category (Admin) | Yes (Admin) |
| PUT | `/categories/{id}` | Update category (Admin) | Yes (Admin) |
| DELETE | `/categories/{id}` | Delete category (Admin) | Yes (Admin) |

---

## 4. Cart (`/api/cart`)

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| GET | `/cart` | Get cart content | Yes |
| POST | `/cart` | Add item to cart | Yes |
| PUT | `/cart/{id}` | Update item quantity | Yes |
| DELETE | `/cart/{id}` | Remove item | Yes |
| DELETE | `/cart/clear` | Clear cart | Yes |

---

## 5. Orders (`/api/orders`)

| Method | Endpoint | Description | Auth Required |
| :--- | :--- | :--- | :--- |
| GET | `/orders` | List orders | Yes |
| GET | `/orders/{id}` | Get order details | Yes |
| POST | `/orders` | Create order | Yes |
| PUT | `/orders/{id}/cancel` | Cancel order | Yes |
| PUT | `/orders/{id}` | Update status (Admin) | Yes (Admin) |

---

## Response Format

### Success
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { ... }
}
```

### Error
```json
{
  "success": false,
  "message": "Error description",
  "errors": { "field": "error message" }
}
```
