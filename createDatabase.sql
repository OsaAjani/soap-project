#Fichier de création de la base
CREATE DATABASE IF NOT EXISTS soap_project;
use soap_project;

CREATE TABLE IF NOT EXISTS driver
(
	id INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(25) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS truck
(
	id INT NOT NULL AUTO_INCREMENT,
	matriculation VARCHAR(25) NOT NULL,
	PRIMARY KEY (id),
	UNIQUE (matriculation)
);

CREATE TABLE IF NOT EXISTS path
(
	id INT NOT NULL AUTO_INCREMENT,
	driver_id INT NOT NULL,
	truck_id INT NOT NULL,
	start_latitude DOUBLE(10,7) NOT NULL,
	start_longitude DOUBLE(10,7) NOT NULL,
	end_latitude DOUBLE(10,7) NOT NULL,
	end_longitude DOUBLE(10,7) NOT NULL,
	status INT NOT NULL,
	start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	end_date TIMESTAMP,
	sms_static BOOLEAN NOT NULL DEFAULT 0,
	PRIMARY KEY (id),
	CONSTRAINT fk_path_driver FOREIGN KEY (driver_id) REFERENCES driver(id)	ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_path_truck FOREIGN KEY (truck_id) REFERENCES truck(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS repairer
(
	id INT NOT NULL AUTO_INCREMENT,
	name VARCHAR(25) NOT NULL,
	phone VARCHAR(20) NOT NULL,
	PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS position
(
	id INT NOT NULL AUTO_INCREMENT,
	path_id INT NOT NULL,
	latitude DOUBLE(10,7) NOT NULL,
	longitude DOUBLE(10,7) NOT NULL,
	at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id),
	CONSTRAINT fk_position_path FOREIGN KEY (path_id) REFERENCES path(id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS intervention
(
	id INT NOT NULL AUTO_INCREMENT,
	path_id INT NOT NULL,
	repairer_id INT NOT NULL,
	status INT NOT NULL,
	start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	end_date TIMESTAMP,
	PRIMARY KEY (id),
	CONSTRAINT fk_intervention_path FOREIGN KEY (path_id) REFERENCES path(id) ON UPDATE CASCADE ON DELETE CASCADE,
	CONSTRAINT fk_intervention_repairer FOREIGN KEY (repairer_id) REFERENCES repairer(id) ON UPDATE CASCADE ON DELETE CASCADE
);

