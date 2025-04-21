INSERT INTO books (author_id, title, url_title, publication_year, description) VALUES
(1, 'Les Métamorphoses', 'les-metamorphoses', 150, 'Roman en prose, rappelant un voyage spirituel mais dans un trait comique'),
(1, 'Apologie', 'apologie', 160, 'Plaidoirie faite par Apulée après avoir été accusé de sorcellerie'),
(34, 'Les Amours', 'les-amours', -16, 'Recueil d''élégies en trois livres où Ovide chante sa passion fictive pour une femme nommée Corinne'),
(40, 'Lettres', 'lettres', 100, 'Recueil de lettres écrites par Pline le Jeune à des amis et à des personnalités de son temps'),
(34, 'Les Métamorphoses', 'les-metamorphoses', 8, 'Poème épique en quinze livres relatant l''histoire du monde depuis sa création à travers des récits de métamorphoses mythologiques');


INSERT INTO book_suggestions (suggestion_id, author_id, title, url_title, publication_year, description) VALUES
    (4, 28, 'De Rerum Natura', 'de-rerum-natura', -55, 'Poème philosophique de Lucrèce sur la nature et l’épicurisme'),
    (7, 34, 'Les Métamorphoses', 'les-metamorphoses', 8, 'Poème épique en quinze livres relatant l''histoire du monde depuis sa création à travers des récits de métamorphoses mythologiques')