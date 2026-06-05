-- Demo data for FillMovie.
-- All three accounts use the password: password123
-- Import after schema.sql.

USE fillmovie;

INSERT INTO users (first_name, last_name, email, password_hash, bio, birth_date) VALUES
('Ada', 'Lin',   'ada@example.com', '$2y$10$hudbTNawhid5p9atz9B5E.ccK86JTTpKOysW8iYjGv9Eeh3U1tXUi', 'Sci-fi and slow-burn thrillers.', '1995-04-12'),
('Leo', 'Marsh',  'leo@example.com', '$2y$10$R9fn13nGFt0pq8tk/tw/iuduxkglH.291Cqi.ODj4vXmq7T4NsCcG', 'Will watch anything by Kurosawa.', '1990-09-03'),
('Mia', 'Okafor', 'mia@example.com', '$2y$10$7ANv5aHAuJeEb3yrmjsIGOsmAvaJjpkxCAt3noP7hC8mcqKChy/ZK', 'Horror in October, musicals all year.', '1998-01-27');

INSERT INTO posts (user_id, body) VALUES
(1, 'Rewatched Blade Runner 2049 tonight. Still the best looking film of the last decade.'),
(2, 'Seven Samurai holds up at three and a half hours. Not a wasted minute.'),
(3, 'Looking for a good comfort movie for a rainy day — recommendations welcome.'),
(1, 'Unpopular opinion: the sequels are better than the original here.');

INSERT INTO reviews (user_id, movie_title, title, body, rating) VALUES
(1, 'Arrival', 'Language as a weapon and a gift', 'A patient, moving take on first contact. The structure pays off completely on a second watch.', 9),
(2, 'Rashomon', 'Truth has no single shape', 'Four accounts, no easy answers. Decades later it still feels modern.', 10),
(3, 'The Babadook', 'Grief with teeth', 'Less about the monster than what it stands for. Tense and sad in equal measure.', 8);

INSERT INTO watchlist (user_id, movie_title, status) VALUES
(1, 'Dune: Part Two', 'watched'),
(1, 'Stalker', 'to_watch'),
(2, 'Ikiru', 'watched'),
(3, 'Hereditary', 'to_watch');

INSERT INTO friendships (follower_id, following_id) VALUES
(1, 2),
(1, 3),
(3, 1);

INSERT INTO messages (sender_id, receiver_id, subject, body) VALUES
(2, 1, 'Recommendation', 'If you liked Arrival, try watching Solaris next.'),
(1, 2, 'Re: Recommendation', 'On my list now, thanks!');
