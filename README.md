# 🎬 AMDB-API

A Laravel-based **Movies & Series API** that provides endpoints to fetch, search, and manage movie and TV show data. Designed to power your frontend apps or public movie listing platforms.

---

## 🚀 Features

* 🎥 Retrieve lists of movies and series
* 🔍 Search movies by title or genre
* 🧾 Fetch detailed info (cast, release year, ratings, etc.)
* 🧠 RESTful API architecture
* 🗄️ Laravel Eloquent ORM for clean database handling
* 🔐 API token authentication (Laravel Sanctum)

---

## 🏗️ Tech Stack

* **Backend:** Laravel 11
* **Database:** MySQL / MariaDB
* **Authentication:** Laravel Sanctum
* **API Docs:** Swagger / Postman
* **Hosting (optional):** Laravel Forge / Vercel / Alwaysdata

---

## ⚙️ Installation

1. **Clone the repo**

   ```bash
   git clone https://github.com/wadecalvin9/AMDB-API-.git
   cd amdb-api
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Copy and edit your environment file**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set your database credentials** in `.env`:

   ```env
   DB_DATABASE=amdb
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations and seeders**

   ```bash
   php artisan migrate --seed
   ```

6. **Start the development server**

   ```bash
   php artisan serve
   ```

   Your API will be live at: **[http://127.0.0.1:8000/api/](http://127.0.0.1:8000/api/)**

---

## 📡 Example Endpoints

| Method | Endpoint                      | Description                      |
| ------ | ----------------------------- | -------------------------------- |
| GET    | `/api/movies`                 | Get all movies                   |
| GET    | `/api/movies/{id}`            | Get a specific movie             |
| GET    | `/api/series`                 | Get all TV series                |
| GET    | `/api/search?query=Inception` | Search by title                  |
| POST   | `/api/movies`                 | Add a new movie (requires token) |

---

## 🔑 Authentication

Use Laravel **Sanctum** tokens to access protected routes:

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" http://127.0.0.1:8000/api/movies
```

## 🧑‍💻 Author

**Wade Calvin**
📧 [your.email@example.com](mailto:wadecalvin9@gmail.com)
🐙 [GitHub](https://github.com/wadecalvin9)

---

## 📜 License

This project is licensed under the **MIT License** — feel free to modify and distribute.

---

## 🌟 Show your support

If you like this project, please ⭐ the repo!
