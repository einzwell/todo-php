# Todo-PHP

A simple CRUD application to manage a list of tasks (todos), written in PHP.

## Description

Todo-PHP is a simple web application that allows users to create, read, update, and delete tasks (a.k.a. todos). 
It is designed to be a lightweight and easy-to-use tool for managing personal tasks and to-do lists.

This application is built as a mid-semester project for the course "Web-Based Programming" at Bina Nusantara University
in 2025.

## Getting Started

### Requirements

[Docker](https://www.docker.com/get-started) or [Podman](https://podman.io/getting-started/installation) is required to run this application. Alternatively, you can run the application using XAMPP or MAMP
(you're on your own though).

### Setting up

* Clone this repository and change directory to the project folder

```bash
git clone https://github.com/einzwell/todo-php.git
cd todo-php
```

* Make sure you have set up the `.env` file. You can copy the [`.env.example`](.env.example) file to `.env` and modify it as needed.

* Run `podman-compose` (or `docker-compose`)

```bash
podman-compose up -d --build
```

If everything goes well, you should be able to access the application at [`http://localhost:8000`](http://localhost:8000). You'll need to register an account first before you can use the application.
Additionally, PhpMyAdmin can be accessed at [`http://localhost:8080`](http://localhost:8080) (credentials depend on your `.env` configuration).

* To stop the application, run:

```bash
podman-compose down
```

## Authors

Yoga Smara ([@einzwell](https://github.com/einzwell))