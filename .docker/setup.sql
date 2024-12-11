-- Create a "setup_complete" table if it doesn't exist
CREATE TABLE IF NOT EXISTS setup_complete (completed BOOLEAN);
-- Check if setup has already run
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM setup_complete WHERE completed = TRUE) THEN
        -- Only run these commands if setup has not been completed
	
CREATE TABLE users (
	uid SERIAL PRIMARY KEY NOT NULL,
	username TEXT NOT NULL,
	password TEXT NOT NULL,
	description TEXT,
	pw TEXT
);

CREATE TABLE tokens (
	tid SERIAL PRIMARY KEY NOT NULL,
	uid INT NOT NULL,
	token TEXT NOT NULL,
	FOREIGN KEY (uid) REFERENCES users (uid)
);

CREATE TABLE class_posts (
	cid SERIAL PRIMARY KEY NOT NULL,
	code TEXT NOT NULL,
	name TEXT NOT NULL,
	professor TEXT NOT NULL,
	ects DECIMAL NOT NULL,
	description TEXT NOT NULL
);

CREATE TABLE motd_images (
	iid SERIAL PRIMARY KEY NOT NULL,
	path TEXT NOT NULL,
	title TEXT NOT NULL
);

INSERT INTO users 
(username, password, description,pw)
VALUES
('admin', '$2y$10$hh9ytMIjsKLw2DPwCID2wuWiq88lORR.dQ1FPWyCJbxulrA5XsRa.', 'BOSS','admin'),
('user1', '$2y$10$1RbyH83a4XR8mErKmDnZhOWGLJxSqaYthuO/6Q6KWn85rmgKrX2Ei', 'Head of Security','user1'),
('user2', '$2y$10$kT73J92rnyZ.DWLerUFOseP/ON4O.TxPgxivuDgiepEAN8LKMTXcK', 'Head of Management','user2');

INSERT INTO class_posts
(code, name, professor, ects, description)
VALUES
('187.B12', 'Denkweisen der Informatik', 'Purgathofer, Peter', 5.5, 'Very easy, but can be a bit frustrating'),
('186.866', 'Algorithmen und Datenstrukturen', 'Kronegger, Martin', 8.0, 'Pretty hard, but very interesting'),
('184.735', 'Einführung in die Künstliche Intelligenz', 'Eiter, Thomas', 3.0, 'Very lucky if you pass. Dont underestimate.'),
('188.982', 'Privacy Enhancing Technologies ', 'Weippl, Edgar', 3.0, 'Very fun, and easy to get a perfect grade. Takes a lot of time.');

INSERT INTO motd_images
(path, title)
VALUES
('images/motd_1.png','TU Library'),
('images/motd_2.png','TU Hauptgebaude'),
('images/motd_3.png','TU Freihaus');

   -- Mark setup as completed
        INSERT INTO setup_complete (completed) VALUES (TRUE);
    END IF;
END $$;
