-- Database schema for Corpus Digitale

-- Authors' table
CREATE TABLE authors (
    author_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    url_name VARCHAR(100) NOT NULL UNIQUE,
    image LONGBLOB DEFAULT NULL,
    birth_year INTEGER,
    death_year INTEGER,
    biography TEXT
);

-- Books' table
CREATE TABLE books (
    book_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    author_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    url_title VARCHAR(255) NOT NULL,
    publication_year INTEGER,
    description TEXT,
    FOREIGN KEY (author_id) REFERENCES authors(author_id)
);

-- Chapters' table
CREATE TABLE chapters (
    chapter_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    book_id INTEGER NOT NULL,
    title VARCHAR(255),
    chapter_number INTEGER NOT NULL,
    content LONGTEXT NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(book_id)
);

-- Users' table
CREATE TABLE users (
    user_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE
);

-- Suggestions table (for new content)
CREATE TABLE suggestions (
    suggestion_id INTEGER PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    suggestion_type VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    admin_notes TEXT,
    reviewed_by INTEGER,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id)
);

-- Suggestions will be separated. Each type of suggestion will have its own table.
CREATE TABLE author_suggestions (
    suggestion_id INTEGER PRIMARY KEY,
    author_name VARCHAR(100) NOT NULL,
    author_url_name VARCHAR(100) NOT NULL UNIQUE,
    author_image LONGBLOB DEFAULT NULL,
    birth_year INTEGER,
    death_year INTEGER,
    biography TEXT,
    FOREIGN KEY (suggestion_id) REFERENCES suggestions(suggestion_id)
);

CREATE TABLE book_suggestions (
    suggestion_id INTEGER PRIMARY KEY,
    author_id INTEGER NOT NULL,
    title VARCHAR(255) NOT NULL,
    url_title VARCHAR(255) NOT NULL,
    publication_year INTEGER,
    description TEXT,
    FOREIGN KEY (author_id) REFERENCES authors(author_id),
    FOREIGN KEY (suggestion_id) REFERENCES suggestions(suggestion_id)
);

CREATE TABLE chapter_suggestions (
    suggestion_id INTEGER PRIMARY KEY,
    book_id INTEGER NOT NULL,
    title VARCHAR(255),
    chapter_number INTEGER NOT NULL,
    content LONGTEXT NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(book_id),
    FOREIGN KEY (suggestion_id) REFERENCES suggestions(suggestion_id)
);