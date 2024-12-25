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