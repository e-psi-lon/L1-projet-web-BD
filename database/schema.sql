-- Database schema for Litterae Aeternae

-- Authors table
CREATE TABLE authors (
    author_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    birth_year INT,
    death_year INT,
    biography TEXT
);

-- Books table
CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    publication_year INT,
    description TEXT,
    FOREIGN KEY (author_id) REFERENCES authors(author_id)
);

-- Chapters table
CREATE TABLE chapters (
    chapter_id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    title VARCHAR(255),
    chapter_number INT NOT NULL,
    content TEXT NOT NULL,
    FOREIGN KEY (book_id) REFERENCES books(book_id)
);

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN
);

-- Suggestions table (for new content)
CREATE TABLE suggestions (
    suggestion_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    suggestion_type VARCHAR(20) NOT NULL,
    content TEXT NOT NULL,
    status VARCHAR(20),
    admin_notes TEXT,
    reviewed_by INT,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (reviewed_by) REFERENCES users(user_id)
);