# FillMovie

A small social network for movie fans, written in plain PHP. Sign up, post about
films, write reviews with a rating, keep a watchlist, follow other people, and send
messages. Movie search is backed by the OMDb API.

This is a from-scratch rewrite of an old learning project. The original was full of
SQL injection holes, stored passwords as MD5, and had a leaked API key in the
source. This version uses PDO prepared statements everywhere, hashes passwords with
bcrypt, adds CSRF protection, and keeps secrets in a `.env` file.

## Features

- Register and sign in (bcrypt password hashing)
- A feed of posts from you and the people you follow
- Movie reviews with an optional 1–10 rating
- A watchlist split into "to watch" and "watched"
- Follow other users and browse their profiles
- Direct messages
- Movie search via OMDb, with one click to add a title to your watchlist or review it

## Tech

- PHP 8 (no framework, no Composer) with PDO
- MySQL / MariaDB
- One small CSS file, no build step
- OMDb API for movie search (optional)

## Requirements

- PHP 8.0+ with the PDO MySQL and cURL extensions (both ship with XAMPP)
- MySQL or MariaDB

## Setup

1. **Get the code** into your web root (for XAMPP, under `htdocs/`):

   ```bash
   git clone https://github.com/Yusues/FillMovie.git
   ```

2. **Create the database.** Import the schema, then the demo data if you want
   something to look at:

   ```bash
   mysql -u root < db/schema.sql
   mysql -u root < db/seed.sql
   ```

   (Or import both files through phpMyAdmin.)

3. **Configure the environment.** Copy the example file and edit it:

   ```bash
   cp .env.example .env
   ```

   Set your database credentials. For movie search, add a free
   [OMDb API key](https://www.omdbapi.com/apikey.aspx) as `OMDB_API_KEY` — leave it
   blank to run without that feature.

4. **Open the app** at `http://localhost/FillMovie/public/`.

   The root URL redirects to `public/`. On a production server, point the document
   root at the `public/` folder instead.

### Demo accounts

If you imported `db/seed.sql`, you can sign in with any of these. The password for
all of them is `password123`:

- `ada@example.com`
- `leo@example.com`
- `mia@example.com`

## Project layout

```
public/   web root — the pages you actually visit, plus assets/
src/       config, PDO connection, auth, helpers, OMDb client, layout partials
db/        schema.sql and seed.sql
```

`src/` and `db/` are blocked from direct web access with an `.htaccess` deny, and
they sit outside `public/` so a correct vhost setup never serves them.

## Security notes

- Every query uses PDO prepared statements with bound parameters.
- Passwords are hashed with `password_hash()` (bcrypt) and checked with
  `password_verify()`.
- Forms are protected with per-session CSRF tokens, and all output is escaped.
- Database credentials and the OMDb key live in `.env`, which is gitignored.

If you are migrating from the old version of this project: the API key that used to
be hardcoded in the source is compromised (it was public on GitHub) — revoke it and
issue a new one.

## License

MIT. See [LICENSE](LICENSE).
