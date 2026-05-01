-- FilmVault Database Schema
-- Group 11 · CMP6210 Cloud Computing
-- Run this first, then seed.sql

CREATE DATABASE IF NOT EXISTS filmvault
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE filmvault;

CREATE TABLE IF NOT EXISTS genres (
    id   INT          NOT NULL AUTO_INCREMENT,
    name VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS movies (
    id              INT           NOT NULL AUTO_INCREMENT,
    title           VARCHAR(200)  NOT NULL,
    genre_id        INT           NOT NULL,
    year            YEAR          NOT NULL,
    director        VARCHAR(100)  NOT NULL,
    synopsis        TEXT,
    poster_filename VARCHAR(100)  NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (genre_id) REFERENCES genres(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
