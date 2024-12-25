--create bdd 
CREATE DATABASE gestion_salle_v2;

-- creation de tableau : user_
create table user_(
    
    
    id int PRIMARY KEY AUTO_INCREMENT ,
    matricule varchar(150) ,
    post ENUM('membre','coach','administration'),
    email varchar(150),
    tel varchar(20),
    supprimer TINYINT(1) DEFAULT 0
    );

    --creation tableau reservation 
    CREATE TABLE reservation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_membre INT,
    matricule VARCHAR(150),
    post ENUM('membre', 'coach', 'administration'),
    date_reservation DATETIME,
    status ENUM('confirme', 'non confirme'),

    FOREIGN KEY (id_membre) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (matricule) REFERENCES user(matricule) ON DELETE CASCADE
);

 
-- add column  nom_activite
ALTER TABLE reservation 
add COLUMN nom_activite text;

--add colomn matricule coach
ALTER TABLE reservation
ADD COLUMN matricule_coach varchar(150);

--create le tableau reservation 
CREATE table activite (
    id int PRIMARY key AUTO_INCREMENT,
    nom_activite text ,
    matricule_coach varchar(150),
    description text,
    FOREIGN KEY(matricule_coach) REFERENCES user_(matricule),
    supprimer TINYINT(1) DEFAULT 0TINYINT(1) DEFAULT 0
    );

--add table
    ALTER TABLE activite
add COLUMN image blob ;