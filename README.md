# Movie Social Media

A small social network for movie fans. Sign up, build a profile, post about films,
keep watched and to-watch lists, review movies, message other users, and look up
movie info. It's a plain PHP + MySQL app that runs on a classic Apache/MySQL stack
such as XAMPP. The interface is in Turkish.

This is an old learning project — see the note at the bottom before putting it
anywhere public.

## Features

- Register and log in
- Profile and account settings, including changing your password
- Post about movies and keep a personal diary feed
- Reviews and a "watched" / "to watch" list
- Friends
- Direct messages and chat between users
- Movie info lookup (an IMDb-style info page)
- A top 10 movies showcase

## Stack

- PHP (using `mysqli`)
- MySQL — the database dump is in [moviedb.sql](moviedb.sql)
- Served by Apache; built and tested on XAMPP

## Setup

1. Put this project under your web root so the `movie/` folder is reachable, e.g.
   under `htdocs/`.
2. Create a MySQL database named `film` and import `moviedb.sql` into it.
3. Database settings live in [movie/php/baglanti.php](movie/php/baglanti.php) —
   by default host `localhost`, user `root`, empty password, database `film` (the
   XAMPP defaults). Adjust them if your setup differs.
4. Open the app in your browser, e.g. `http://localhost/movie/`, and start from the
   register or login page.

## A note on security

This was written while learning PHP. Authentication is basic and not every query
is parameterized, so treat it as a study project — don't expose it on the public
internet without reworking the database access and login handling first.

## License

MIT. See [LICENSE](LICENSE).
