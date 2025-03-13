# NYT Best Sellers API

A Laravel application that integrates with the New York Times Best Sellers API to fetch, store, and serve book data.

## Requirements

- PHP 8.1 or higher
- Composer
- SQLite (or another database of your choice)
- New York Times API key (get one at [developer.nytimes.com](https://developer.nytimes.com/))

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/vothaianh/new-york-times.git
   cd new-york-times
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure your database in the `.env` file. By default, this project uses SQLite.

6. Run migrations:
   ```bash
   php artisan migrate
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

## API Endpoints
Excute curl command to verify it:
```bash
curl --location 'http://localhost:8000/api/best-sellers?author=Annette'
```
