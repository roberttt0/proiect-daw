<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proiect DAW - Biblioteca</title>
    <link rel="stylesheet" href="/resources/css/about.css">
</head>
<body>
    <header>
        <h1>Proiect DAW</h1>
        <h2>Biblioteca</h2>
        <a href="index.php" style="color:#E8491D">Inapoi acasa</a>
    </header>

    <main>
        <section id="descriere">
            <h3>Descrierea proiectului</h3>
            <p>
                Aceasta aplicatie dezvoltata in <strong>PHP</strong> are ca scop gestionarea eficienta a unei biblioteci, 
                atat a listei de utilizatori (incluzand clientii, angajatii si administratorii), cat si a cartilor. 
                Aplicatia permite adaugarea, stergerea, modificarea stocurilor de carti, inregistrarea utilizatorilor si 
                verificarea istoricului cartilor imprumutate, precum si a timpului ramas pana la returnare.
            </p>
            <p>
                Include functionalitate de cautare avansata (dupa an, categorie, autor etc.) pentru a ajuta 
                utilizatorii sa gaseasca rapid cartile dorite. De asemenea, poate genera rapoarte — cum ar fi numarul 
                utilizatorilor inregistrati intr-o anumita luna sau numarul de carti imprumutate.
            </p>
        </section>

        <section id="tabele">
            <h3>Tabele SQL</h3>
            
            <div class="sql-block">
<pre><code>CREATE TABLE UTILIZATOR (
    id_utilizator INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nume VARCHAR(100) NOT NULL,
    prenume VARCHAR(100) NOT NULL,
    data_nasterii DATE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    telefon VARCHAR(15),
    data_inscriere DATE DEFAULT CURRENT_DATE,
    parola VARCHAR(255) NOT NULL,
    rol ENUM('membru', 'bibliotecar', 'admin') NOT NULL DEFAULT 'membru'
);</code></pre>
            </div>

            <div class="sql-block">
<pre><code>CREATE TABLE AUTOR (
    id_autor INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nume VARCHAR(150) NOT NULL,
    nationalitate VARCHAR(100),
    data_nasterii DATE,
    descriere TEXT
);</code></pre>
            </div>

            <div class="sql-block">
<pre><code>CREATE TABLE EDITURA (
    id_editura INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    nume VARCHAR(150) NOT NULL,
    oras VARCHAR(100),
    tara VARCHAR(100)
);</code></pre>
            </div>

            <div class="sql-block">
<pre><code>CREATE TABLE CARTE (
    id_carte INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    titlu VARCHAR(255) NOT NULL,
    id_autor INT NOT NULL,
    id_editura INT NOT NULL,
    an_publicare YEAR,
    categorie VARCHAR(100),
    stoc_total INT NOT NULL DEFAULT 0,
    stoc_disponibil INT NOT NULL DEFAULT 0,
    coperta VARCHAR(255),
    FOREIGN KEY (id_autor) REFERENCES AUTOR(id_autor)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_editura) REFERENCES EDITURA(id_editura)
        ON DELETE CASCADE ON UPDATE CASCADE
);</code></pre>
            </div>

            <div class="sql-block">
<pre><code>CREATE TABLE IMPRUMUT (
    id_imprumut INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_utilizator INT NOT NULL,
    id_carte INT NOT NULL,
    data_imprumut DATE NOT NULL DEFAULT CURRENT_DATE,
    data_scadenta DATE NOT NULL,
    data_retur DATE,
    FOREIGN KEY (id_utilizator) REFERENCES UTILIZATOR(id_utilizator)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_carte) REFERENCES CARTE(id_carte)
        ON DELETE CASCADE ON UPDATE CASCADE
);</code></pre>
            </div>

            <div class="sql-block">
<pre><code>CREATE TABLE RECENZIE (
    id_recenzie INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_carte INT NOT NULL,
    id_utilizator INT NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comentariu TEXT,
    data DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (id_carte) REFERENCES CARTE(id_carte)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_utilizator) REFERENCES UTILIZATOR(id_utilizator)
        ON DELETE CASCADE ON UPDATE CASCADE
);</code></pre>
            </div>

            <div class="sql-block">
<pre><code>CREATE TABLE PENALIZARE (
    id_penalizare INT PRIMARY KEY AUTO_INCREMENT NOT NULL,
    id_utilizator INT NOT NULL,
    id_carte INT NOT NULL,
    suma DECIMAL(10,2) NOT NULL CHECK (suma >= 0),
    motiv VARCHAR(255) NOT NULL,
    status ENUM('neachitata', 'achitata') DEFAULT 'neachitata',
    FOREIGN KEY (id_utilizator) REFERENCES UTILIZATOR(id_utilizator)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_carte) REFERENCES CARTE(id_carte)
        ON DELETE CASCADE ON UPDATE CASCADE
);</code></pre>
            </div>
        </section>

        <section id="relatii">
            <h3>Descrierea relatiilor dintre tabele</h3>
            <ul>
                <li><strong>UTILIZATOR - IMPRUMUT:</strong> Relatie intre entitatile UTILIZATOR si IMPRUMUT, indicand ce imprumuturi a facut fiecare utilizator. Cardinalitate minima 1:0 (utilizatorul poate sa nu fi imprumutat nici o carte), maxima 1:n (un utilizator poate imprumuta mai multe carti)</li>
                <li><strong>CARTE - IMPRUMUT:</strong> Relatie intre entitatile CARTE si IMPRUMUT, indicand ce carti au fost imprumutate. Cardinalitate minima 1:0 (o carte poate sa nu fie imprumutata), maxima 1:n (aceeasi carte poate sa fie imprumutata de mai multe ori)</li>
                <li><strong>UTILIZATOR - RECENZIE:</strong> Relatie intre entitatile UTILIZATOR si RECENZIE, indicand recenziile scrise de utilizatori. Cardinalitate minima 1:0 (un utilizator poate sa nu fi scris nici o recenzie), maxima 1:n (un utilizator poate scrie mai multe recenzii)</li>
                <li><strong>CARTE - RECENZIE:</strong> Relatie intre entitatile CARTE si RECENZIE, indicand recenziile scrise pentru carti. Cardinalitate minima 1:0 (o carte poate sa nu aiba nici o recenzie), maxima 1:n (o carte poate avea mai multe recenzii)</li>
                <li><strong>EDITURA - CARTE:</strong> Relatie intre entitatile EDITURA si CARTE, indicand cartile detinute de o editura. Cardinalitate minima 1:1 (o editura poate detine minim o carte), maxima 1:n (o editura poate detine mai multe carti)</li>
                <li><strong>AUTOR - CARTE:</strong> Relatie intre entitatile AUTOR si CARTE, indicand cartile scrise de autori. Cardinalitate minima 1:1 (un autor a scris minim o carte), maxima 1:n (un autor poate sa fi scris mai multe carti)</li>
                <li><strong>CARTE - PENALIZARE:</strong> Relatie intre entitatile CARTE si PENALIZARE, indicand ce carte apartine unei penalizari. Cardinalitate minima 1:0 (o carte poate sa nu apartina nici unei penalizari), maxima 1:n (o carte poate sa apartina la mai multe penalizari)</li>
                <li><strong>UTILIZATOR - PENALIZARE:</strong> Relatie intre entitatile UTILIZATOR si PENALIZARE, indicand ce penalizari are fiecare utilizator. Cardinalitate minima 1:0 (un utilizator poate sa nu aiba nici o penalizare), maxima 1:n (un utilizator poate avea mai multe penalizari).</li>
            </ul>
        </section>

        <section id="imagini">
            <h3>Diagrama conceptuala</h3>
            <img src="resources/img/diagrama.png" />
            
            <h3>Diagrame UML</h3>
            <img src="resources/img/uml1.png" />
            <img src="resources/img/uml2.png" />
        </section>
    </main>

    <footer>
        <p>&copy; 2025 - Proiect DAW - Biblioteca</p>
        <p class="author">Realizat de <strong>Antohi Robert</strong></p>
    </footer>
</body>
</html>
