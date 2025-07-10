## Installation & Setup

1.  **Clone the repository**

    ```bash
    git clone https://github.com/Rendisaputra33/technical-test-management-product.git
    cd management-product
    ```

2.  **Install PHP dependencies**

    ```bash
    composer install
    ```

3.  **Setup environment**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Configure environtment variable in `.env`**

    ```env
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=management_product
    DB_USERNAME=root
    DB_PASSWORD=root

    JWT_SECRET=scret
    JWT_REFRESH_SECRET=scret
    ```

5.  **Run Application**

    ```bash
    docker compose up -d
    ```

6.  **Run migrations**

    ```bash
    docker exec -it management_product_app php artisan migrate:fresh
    ```

    ## Postman Collection

    ```web
    https://documenter.getpostman.com/view/15324133/2sB34fm1Rg
    ```
