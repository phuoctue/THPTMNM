# RESTful API Migration Guide for `project1`

Tài liệu này là blueprint để chuyển website e-commerce MVC PHP hiện tại sang kiến trúc RESTful API tách biệt Backend API và Frontend.

## 1) Nên dùng Laravel hay PHP thuần?

### Khuyến nghị
- **PHP thuần** phù hợp nếu:
  - bạn muốn tận dụng code hiện tại và chuyển đổi từng bước
  - dự án nhỏ, team muốn kiểm soát toàn bộ framework
  - chấp nhận tự build các lớp nền như router, response, validator, middleware

### Gợi ý thực tế cho dự án này
- Nếu mục tiêu là **chuyển toàn bộ hệ thống lâu dài**, nên **rewrite API bằng Laravel**.
- Nếu muốn **giảm rủi ro và tận dụng code hiện có**, có thể làm **API layer trong PHP thuần** trước, rồi frontend mới gọi API.

## 2) Nguyên tắc thiết kế API

- **Stateless**: mỗi request tự chứa thông tin xác thực.
- **Resource-based**: endpoint xoay quanh tài nguyên, không xoay quanh action.
- **HTTP method đúng chuẩn**:
  - `GET` lấy dữ liệu
  - `POST` tạo mới
  - `PUT`/`PATCH` cập nhật
  - `DELETE` xóa
- **JSON** là format mặc định.
- **Versioning** bắt buộc: `/api/v1/...`
- **Chuẩn response thống nhất**:

```json
{
  "success": true,
  "message": "OK",
  "data": {},
  "errors": null,
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 120,
      "last_page": 8
    }
  }
}
```

## 3) Cấu trúc thư mục đề xuất

### Nếu dùng Laravel

```txt
app/
  Http/
    Controllers/
      Api/
        V1/
          AuthController.php
          ProductController.php
          CategoryController.php
          UserController.php
          OrderController.php
          CartController.php
          ReviewController.php
          PaymentController.php
          BannerController.php
          PromotionController.php
    Requests/
      Api/
        V1/
    Middleware/
  Models/
  Services/
  Repositories/
  Policies/
  Exceptions/
  Resources/
routes/
  api.php
  web.php
database/
  migrations/
  seeders/
config/
```

### Nếu giữ PHP thuần

```txt
app/
  api/
    bootstrap.php
    routes.php
    v1/
      controllers/
      requests/
      services/
      repositories/
      middleware/
      resources/
      helpers/
      exceptions/
  controllers/
  models/
  libs/
public/
  index.php
uploads/
```

## 4) Recommendation về Authentication

### Laravel
- **Sanctum**: phù hợp nhất cho SPA, mobile app, frontend tách biệt nhưng vẫn thuộc hệ thống của bạn.
- **JWT**: phù hợp nếu API cần client bên ngoài, microservice, hoặc muốn bearer token thuần.

### Khuyến nghị thực tế
- Nếu frontend của bạn là SPA/Next.js/Vue/React do chính bạn quản lý: **Sanctum**.
- Nếu bạn cần external client hoặc muốn token không phụ thuộc cookie: **JWT**.

## 5) Middleware cần có

- `auth`
- `guest`
- `admin`
- `customer`
- `verified`
- `throttle`
- `cors`
- `json.response`

## 6) Response format chuẩn

### Success

```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 15
  },
  "errors": null
}
```

### Validation error

```json
{
  "success": false,
  "message": "Validation failed",
  "data": null,
  "errors": {
    "name": ["The name field is required."]
  }
}
```

### Server error

```json
{
  "success": false,
  "message": "Internal server error",
  "data": null,
  "errors": null
}
```

## 7) API Resource chính và endpoint

Base URL mẫu:

```txt
/api/v1
```

### 7.1 Auth

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/auth/register` | Đăng ký |
| POST | `/auth/login` | Đăng nhập |
| POST | `/auth/logout` | Đăng xuất |
| POST | `/auth/refresh` | Làm mới token |
| GET | `/auth/me` | Thông tin user hiện tại |
| POST | `/auth/forgot-password` | Gửi link quên mật khẩu |
| POST | `/auth/reset-password` | Đặt lại mật khẩu |
| POST | `/auth/verify-email` | Xác thực email |
| POST | `/auth/resend-verification` | Gửi lại email xác thực |

### 7.2 Products

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/products` | Danh sách sản phẩm |
| POST | `/products` | Tạo sản phẩm |
| GET | `/products/{id}` | Chi tiết sản phẩm |
| PUT/PATCH | `/products/{id}` | Cập nhật sản phẩm |
| DELETE | `/products/{id}` | Xóa sản phẩm |
| GET | `/products/{id}/reviews` | Danh sách review của sản phẩm |
| POST | `/products/{id}/reviews` | Tạo review cho sản phẩm |

### 7.3 Categories

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/categories` | Danh sách danh mục |
| POST | `/categories` | Tạo danh mục |
| GET | `/categories/{id}` | Chi tiết danh mục |
| PUT/PATCH | `/categories/{id}` | Cập nhật danh mục |
| DELETE | `/categories/{id}` | Xóa danh mục |
| GET | `/categories/{id}/products` | Sản phẩm thuộc danh mục |

### 7.4 Users

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/users/me` | Hồ sơ cá nhân |
| PUT/PATCH | `/users/me` | Cập nhật hồ sơ |
| POST | `/users/me/avatar` | Upload avatar |
| POST | `/users/me/password` | Đổi mật khẩu |
| GET | `/users` | Danh sách user admin |
| GET | `/users/{id}` | Chi tiết user admin |
| PUT/PATCH | `/users/{id}` | Cập nhật user admin |
| DELETE | `/users/{id}` | Xóa/soft delete user |

### 7.5 Orders

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/orders` | Danh sách đơn hàng |
| POST | `/orders` | Tạo đơn hàng |
| GET | `/orders/{id}` | Chi tiết đơn hàng |
| PUT/PATCH | `/orders/{id}` | Cập nhật đơn hàng |
| DELETE | `/orders/{id}` | Hủy/xóa đơn hàng |
| POST | `/orders/{id}/cancel` | Hủy đơn hàng |
| POST | `/orders/{id}/status` | Cập nhật trạng thái cho admin |
| GET | `/orders/{id}/items` | Danh sách order items |

### 7.6 Order Items

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/orders/{orderId}/items` | Danh sách items |
| POST | `/orders/{orderId}/items` | Thêm item |
| PATCH | `/orders/{orderId}/items/{itemId}` | Cập nhật số lượng |
| DELETE | `/orders/{orderId}/items/{itemId}` | Xóa item |

### 7.7 Cart

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/cart` | Xem giỏ hàng |
| POST | `/cart/items` | Thêm sản phẩm vào giỏ |
| PATCH | `/cart/items/{productId}` | Cập nhật số lượng |
| DELETE | `/cart/items/{productId}` | Xóa item |
| DELETE | `/cart/clear` | Xóa toàn bộ giỏ |

### 7.8 Reviews / Comments

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/reviews` | Danh sách review |
| POST | `/reviews` | Tạo review |
| GET | `/reviews/{id}` | Chi tiết review |
| PATCH | `/reviews/{id}` | Cập nhật review |
| DELETE | `/reviews/{id}` | Xóa review |

### 7.9 Payments

| Method | Endpoint | Mô tả |
|---|---|---|
| POST | `/payments/checkout` | Tạo phiên thanh toán |
| POST | `/payments/webhook` | Nhận webhook từ cổng thanh toán |
| GET | `/payments/{id}` | Chi tiết thanh toán |
| GET | `/orders/{id}/payment` | Thanh toán của một đơn |

### 7.10 Banners / Promotions

| Method | Endpoint | Mô tả |
|---|---|---|
| GET | `/banners` | Danh sách banner |
| POST | `/banners` | Tạo banner |
| PUT/PATCH | `/banners/{id}` | Cập nhật banner |
| DELETE | `/banners/{id}` | Xóa banner |
| GET | `/promotions` | Danh sách khuyến mãi |
| POST | `/promotions` | Tạo khuyến mãi |
| PUT/PATCH | `/promotions/{id}` | Cập nhật khuyến mãi |
| DELETE | `/promotions/{id}` | Xóa khuyến mãi |

## 8) Ví dụ chi tiết: Product API

### `GET /api/v1/products`

Query params gợi ý:
- `page`
- `per_page`
- `search`
- `category_id`
- `min_price`
- `max_price`
- `sort_by`
- `sort_dir`

Response:

```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "iPhone 15 Pro",
      "price": 29990000,
      "image": "uploads/xxx.jpg",
      "category": {
        "id": 1,
        "name": "Điện thoại"
      }
    }
  ],
  "meta": {
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 120,
      "last_page": 8
    }
  }
}
```

### `POST /api/v1/products`

Content type:
- `application/json` cho dữ liệu thường
- `multipart/form-data` nếu upload ảnh

Fields:
- `name`
- `description`
- `price`
- `category_id`
- `image`
- `status`
- `sku` nếu có

### `PUT/PATCH /api/v1/products/{id}`
- `PUT`: thay thế toàn bộ dữ liệu
- `PATCH`: cập nhật từng phần

### Upload hình ảnh sản phẩm
- lưu file vào `uploads/products`
- chỉ cho phép `jpg`, `jpeg`, `png`, `webp`
- validate:
  - kích thước file
  - MIME type
  - random filename
  - không dùng tên gốc từ client

## 9) Ví dụ chi tiết: Order API

### `POST /api/v1/orders`

Request body:

```json
{
  "customer_name": "Nguyen Van A",
  "customer_phone": "0909123456",
  "customer_email": "vana@gmail.com",
  "customer_address": "TP.HCM",
  "note": "Giao giờ hành chính",
  "payment_method": "cod",
  "items": [
    {
      "product_id": 1,
      "quantity": 1
    },
    {
      "product_id": 2,
      "quantity": 2
    }
  ]
}
```

### `GET /api/v1/orders/{id}`
Response nên trả:
- thông tin order
- danh sách order items
- tổng tiền
- trạng thái thanh toán
- trạng thái xử lý

### `PATCH /api/v1/orders/{id}`
Chỉ cho phép cập nhật một số trường hợp hợp lệ:
- `note`
- `customer_phone`
- `customer_address`
- `status`
- `payment_status`

### `POST /api/v1/orders/{id}/status`
Chỉ admin hoặc staff được phép gọi.

## 10) Chuyển từ MVC sang API như thế nào?

### Bước 1: Tách Controller cũ
Controller hiện tại đang làm 3 việc:
- nhận request
- xử lý nghiệp vụ
- render view

Trong API, controller chỉ nên:
- nhận input
- gọi service
- trả về JSON

### Bước 2: Tách Business Logic ra Service
Ví dụ:
- `ProductController` chỉ gọi `ProductService`
- `ProductService` xử lý:
  - validate business rules
  - tính toán giá
  - mapping dữ liệu
  - transaction

### Bước 3: Đưa SQL sang Repository
Repository chịu trách nhiệm:
- query DB
- CRUD
- filter/search/sort
- transactions nếu cần

### Bước 4: View → JSON
Toàn bộ `include view` sẽ được thay bằng:

```php
return JsonResponse::success($data);
```

### Bước 5: Pagination, filtering, sorting, search

#### Pagination
- `page`
- `per_page`

#### Filtering
- `category_id`
- `status`
- `price_min`
- `price_max`

#### Sorting
- `sort_by=created_at`
- `sort_dir=desc`

#### Search
- `search=iphone`

### Bước 6: Upload ảnh
- chuyển sang `multipart/form-data`
- API chỉ nhận file và metadata
- trả về URL/path file đã lưu

## 11) Gợi ý chuyển từng controller hiện tại

### `AuthController`
- `register()` → `POST /api/v1/auth/register`
- `login()` → `POST /api/v1/auth/login`
- `logout()` → `POST /api/v1/auth/logout`
- `verifyEmail()` → `POST /api/v1/auth/verify-email`
- `forgotPassword()` → `POST /api/v1/auth/forgot-password`
- `resetPassword()` → `POST /api/v1/auth/reset-password`

### `ProductController`
- `index()` → `GET /api/v1/products`
- `show()` → `GET /api/v1/products/{id}`
- `save()` → `POST /api/v1/products`
- `update()` → `PUT/PATCH /api/v1/products/{id}`
- `delete()` → `DELETE /api/v1/products/{id}`

### `CategoryController`
- `index()` → `GET /api/v1/categories`
- `save()` → `POST /api/v1/categories`
- `update()` → `PUT/PATCH /api/v1/categories/{id}`
- `delete()` → `DELETE /api/v1/categories/{id}`

### `CartController`
- `index()` → `GET /api/v1/cart`
- `add()` → `POST /api/v1/cart/items`
- `update()` → `PATCH /api/v1/cart/items/{productId}`
- `remove()` → `DELETE /api/v1/cart/items/{productId}`
- `placeOrder()` → `POST /api/v1/orders`

### `User/Profile`
- `profile()` → `GET /api/v1/users/me`
- `updateProfile()` → `PATCH /api/v1/users/me`
- `changePassword()` → `POST /api/v1/users/me/password`
- `updateAvatar()` → `POST /api/v1/users/me/avatar`

## 12) Error handling chuẩn

Nên chuẩn hóa theo HTTP status code:
- `200` OK
- `201` Created
- `204` No Content
- `400` Bad Request
- `401` Unauthorized
- `403` Forbidden
- `404` Not Found
- `422` Validation Error
- `429` Too Many Requests
- `500` Internal Server Error

## 13) Rate limiting và CORS

### Rate limiting
- login/register/forgot-password: giới hạn chặt
- product listing: giới hạn mềm
- webhook payment: cho whitelist IP/signature

### CORS
- chỉ cho phép domain frontend thật sự cần
- bật credentials nếu dùng cookie-based auth
- xác định rõ allowed headers/methods

## 14) Database notes

Hiện DB của bạn đã có:
- `users`
- `category`
- `product`
- `orders`
- `order_items`

Nên bổ sung nếu làm API đầy đủ:
- `carts`
- `cart_items`
- `reviews`
- `payments`
- `banners`
- `promotions`
- `tokens` hoặc bảng tương đương tùy auth strategy

## 15) Lộ trình chuyển đổi an toàn

### Phase 1
- dựng API version `v1`
- chuyển `auth`, `products`, `categories`

### Phase 2
- chuyển `cart`, `orders`, `users`

### Phase 3
- thêm `reviews`, `payments`, `banners`, `promotions`

### Phase 4
- tách frontend hoàn toàn sang SPA/Next.js/Vue/React

## 16) Kết luận

- Nếu ưu tiên **ổn định và phát triển lâu dài**, chọn **Laravel + Sanctum**.
- Nếu ưu tiên **tận dụng code hiện tại**, chuyển dần sang **API layer PHP thuần** rồi tái cấu trúc từng phần.
- Dù chọn hướng nào, hãy giữ:
  - versioning
  - response format thống nhất
  - validation rõ ràng
  - auth middleware
  - service/repository layer

