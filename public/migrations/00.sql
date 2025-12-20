CREATE DATABASE biblioteca_db;
USE biblioteca_db;

-- 1. Tabela Autor
CREATE TABLE Autor (
    id_autor INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100),
    nationalitate VARCHAR(50)
);

-- 2. Tabela Editura
CREATE TABLE Editura (
    id_editura INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(100),
    oras VARCHAR(50)
);

-- 3. Tabela Utilizator
CREATE TABLE Utilizator (
    id_utilizator INT AUTO_INCREMENT PRIMARY KEY,
    nume VARCHAR(50),
    prenume VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    parola VARCHAR(255),
    rol VARCHAR(20) DEFAULT 'user'
);

-- 4. Tabela Carte
CREATE TABLE Carte (
    id_carte INT AUTO_INCREMENT PRIMARY KEY,
    titlu VARCHAR(150),
    id_autor INT,
    id_editura INT,
    an_publicare INT,
    stoc_total INT,
    stoc_disponibil INT,
    FOREIGN KEY (id_autor) REFERENCES Autor(id_autor),
    FOREIGN KEY (id_editura) REFERENCES Editura(id_editura)
);

-- 5. Tabela Imprumut
CREATE TABLE Imprumut (
    id_imprumut INT AUTO_INCREMENT PRIMARY KEY,
    id_utilizator INT,
    id_carte INT,
    data_imprumut DATE,
    data_scadenta DATE,
    data_retur DATE NULL,
    FOREIGN KEY (id_utilizator) REFERENCES Utilizator(id_utilizator),
    FOREIGN KEY (id_carte) REFERENCES Carte(id_carte)
);

-- 6. Tabela Recenzie
CREATE TABLE Recenzie (
    id_recenzie INT AUTO_INCREMENT PRIMARY KEY,
    id_carte INT,
    id_utilizator INT,
    rating INT,
    comentariu TEXT,
    data DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_carte) REFERENCES Carte(id_carte),
    FOREIGN KEY (id_utilizator) REFERENCES Utilizator(id_utilizator)
);

-- Date de test
INSERT INTO Autor (nume, nationalitate) VALUES ('Mihai Eminescu', 'Romana'), ('J.K. Rowling', 'Britanica');
INSERT INTO Editura (nume, oras) VALUES ('Humanitas', 'Bucuresti'), ('Arthur', 'Londra');
INSERT INTO Carte (titlu, id_autor, id_editura, an_publicare, stoc_total, stoc_disponibil) VALUES 
('Poezii', 1, 1, 1883, 5, 5),
('Harry Potter', 2, 2, 1997, 3, 3);