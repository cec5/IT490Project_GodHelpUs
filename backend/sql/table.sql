/*table that stores users*/
CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
 	username VARCHAR(50) NOT NULL UNIQUE,
  	email VARCHAR(100) NOT NULL,
  	password VARCHAR(255) NOT NULL,
  	epoch INT(11) NOT NULL,
	phoneNum varchar(15) DEFAULT NULL,
  	`2fa` tinyint(1) NOT NULL DEFAULT 0,
  	code char(4) DEFAULT NULL
);

/*table that stores simple info from matches*/
CREATE TABLE matches (
    	id INT AUTO_INCREMENT PRIMARY KEY,
    	date DATETIME NOT NULL,
    	home_team VARCHAR(100) NOT NULL,
    	away_team VARCHAR(100) NOT NULL,
    	score_home TINYINT,
    	score_away TINYINT
);

/*table that stores leagues*/
CREATE TABLE leagues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

/*table that keeps track what users are part of what leagues*/
CREATE TABLE user_league (
    	user_id INT,
    	league_id INT,
    	points INT DEFAULT 0,
    	PRIMARY KEY (user_id, league_id),
    	FOREIGN KEY (user_id) REFERENCES users(id),
    	FOREIGN KEY (league_id) REFERENCES leagues(id)
);

/*table that keeps track of league board messages*/
CREATE TABLE messages (
    	id INT AUTO_INCREMENT PRIMARY KEY,
    	league_id INT,
    	user_id INT,
    	message TEXT NOT NULL,
    	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    	FOREIGN KEY (league_id) REFERENCES leagues(id),
    	FOREIGN KEY (user_id) REFERENCES users(id)
);

/*table that creates the player table */
CREATE TABLE players (
	id INT PRIMARY KEY,
	name VARCHAR(50),
	nationality VARCHAR(50),
	position VARCHAR(50),
	team VARCHAR(50)
);

/*stores selected players by users per league*/
CREATE TABLE user_draft (
    	user_id INT,
    	league_id INT,
    	player_id INT,
    	position VARCHAR(50),
    	status ENUM('active', 'reserve') NOT NULL,
    	PRIMARY KEY (user_id, league_id, player_id),
    	FOREIGN KEY (user_id) REFERENCES users(id),
    	FOREIGN KEY (league_id) REFERENCES leagues(id),
    	FOREIGN KEY (player_id) REFERENCES players(id)
);

CREATE TABLE trades (
    	id INT AUTO_INCREMENT PRIMARY KEY,
    	proposing_user_id INT NOT NULL,
    	receiving_user_id INT NOT NULL,
    	league_id INT NOT NULL,
    	proposed_player_id INT NOT NULL,
    	requested_player_id INT NOT NULL,
    	status ENUM('pending', 'accepted', 'declined', 'withdrawn') DEFAULT 'pending',
    	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    	FOREIGN KEY (proposing_user_id) REFERENCES users(id),
    	FOREIGN KEY (receiving_user_id) REFERENCES users(id),
    	FOREIGN KEY (league_id) REFERENCES leagues(id),
    	FOREIGN KEY (proposed_player_id) REFERENCES players(id),
    	FOREIGN KEY (requested_player_id) REFERENCES players(id)
);

