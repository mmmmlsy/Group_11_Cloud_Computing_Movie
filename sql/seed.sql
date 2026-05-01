-- FilmVault Seed Data — 10 movies across 5 genres
-- Group 11 · CMP6210 Cloud Computing
-- Run after schema.sql

USE filmvault;

INSERT INTO genres (name) VALUES
    ('Action'),
    ('Drama'),
    ('Sci-Fi'),
    ('Crime'),
    ('Animation');

-- genre_id: 1=Action, 2=Drama, 3=Sci-Fi, 4=Crime, 5=Animation
INSERT INTO movies (title, genre_id, year, director, synopsis, poster_filename) VALUES
(
    'The Shawshank Redemption', 2, 1994, 'Frank Darabont',
    'Wrongly convicted of murder, banker Andy Dufresne forges an unlikely friendship with fellow prisoner Red while maintaining his innocence and searching for a way to reclaim his freedom inside the walls of Shawshank State Penitentiary.',
    'shawshank.jpg'
),
(
    'The Dark Knight', 1, 2008, 'Christopher Nolan',
    'Batman raises the stakes in his war on crime. With the help of Lt. Jim Gordon and District Attorney Harvey Dent, he sets out to dismantle the remaining criminal organisations that plague the streets. But a rising criminal mastermind known as the Joker throws Gotham into chaos.',
    'dark_knight.jpg'
),
(
    'Inception', 3, 2010, 'Christopher Nolan',
    'A skilled thief who steals corporate secrets through dream-sharing technology is offered a chance to have his criminal record erased if he can successfully plant an idea in a CEO''s subconscious.',
    'inception.jpg'
),
(
    'The Godfather', 4, 1972, 'Francis Ford Coppola',
    'The ageing patriarch of an organised crime dynasty transfers control of his clandestine empire to his reluctant son, triggering a bloody power struggle among the families of New York.',
    'godfather.jpg'
),
(
    'Pulp Fiction', 4, 1994, 'Quentin Tarantino',
    'The lives of two mob hitmen, a boxer, a gangster and his wife, and a pair of diner bandits interweave in four tales of violence and redemption in the Los Angeles underworld.',
    'pulp_fiction.jpg'
),
(
    'Interstellar', 3, 2014, 'Christopher Nolan',
    'A team of explorers travel through a wormhole in space in an attempt to ensure humanity''s survival. When Earth''s crops begin to fail, a former NASA pilot is recruited to lead a desperate mission beyond our solar system.',
    'interstellar.jpg'
),
(
    'The Matrix', 3, 1999, 'Lana Wachowski, Lilly Wachowski',
    'A computer programmer discovers that reality as he knows it is a simulation created by machines. Freed from the illusion, he joins a rebellion against the machine overlords.',
    'matrix.jpg'
),
(
    'Toy Story', 5, 1995, 'John Lasseter',
    'A cowboy doll is profoundly threatened and jealous when a new spaceman figure supplants him as top toy in a boy''s room. Together they must escape a dangerous neighbour and find their way home.',
    'toy_story.jpg'
),
(
    'Forrest Gump', 2, 1994, 'Robert Zemeckis',
    'The presidencies of Kennedy and Johnson, Vietnam, Watergate, and other historical events unfold from the perspective of an Alabama man with an IQ of 75, whose only desire is to be reunited with his childhood sweetheart.',
    'forrest_gump.jpg'
),
(
    'Mad Max: Fury Road', 1, 2015, 'George Miller',
    'In a post-apocalyptic wasteland, a woman rebels against a tyrannical ruler in search of her homeland with the help of a group of female prisoners, a psychotic worshipper and a drifter named Max.',
    'mad_max.jpg'
);
