# Smash Badminton Shop

Laravel + MySQL web shop for badminton gear. Needs Docker and Node.js on your machine; PHP and MySQL run in containers.

## Run

First time only:

```sh
cp .env.example .env
docker compose build
docker compose run --rm app composer install
docker compose run --rm app php artisan key:generate
npm install
npm run build
```

Start the app and load demo data:

```sh
docker compose up -d
docker compose exec app php artisan migrate:fresh --seed
```

Open http://localhost:8000.

Next times, only `docker compose up -d` is needed. Stop with `docker compose down`.

`migrate:fresh --seed` wipes the database and restores the demo data (24 products, 3 customers, sample orders and reviews). Run it again any time to reset.

After editing Blade views or CSS, run `npm run build` again (or `npm run dev` while working).

## Default accounts

All passwords are `password`.

| Role | Email | Password |
|---|---|---|
| Admin | `admin@shop.test` | `password` |
| Customer | `customer1@shop.test` | `password` |
| Customer | `customer2@shop.test` | `password` |
| Customer | `customer3@shop.test` | `password` |

Admins get an **Admin** button in the navbar (dashboard, products, orders, users, reviews). New sign-ups through the Register page are customers.
