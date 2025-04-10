<?php

namespace Todo\models;

require_once(__DIR__ . '/../config/Database.php');
use Todo\config\Database;

class User {
    private \PDO $db;

    public string $id;
    public string $username;
    private string $password;
    public ?string $error = null;

    public function __construct(string $username, string $password) {
        $db = new Database();
        $this->db = $db->connection;
        $this->username = $username;
        $this->password = $password;
    }

    public function create(): bool {
        $this->error = null;

        // Sanity check
        if ($this->get() !== null) {
            $this->error = "User with the same username already exists.";
            return false;
        }

        $query = <<<EOS
            INSERT INTO users
                (username, password)
            VALUES
                (:username, :password);
        EOS;
        $stmt = $this->db->prepare($query);

        try {
            return $stmt->execute([
                ':username' => $this->username,
                ':password' => password_hash($this->password, PASSWORD_BCRYPT)
            ]);
        } catch (\PDOException $e) {
            $this->error = "Error creating user: " . $e->getMessage();
            return false;
        }
    }

    private function get(): ?array {
        $this->error = null;

        $query = <<<EOS
            SELECT id, username, password
            FROM users
            WHERE username = :username
            LIMIT 1;
        EOS;
        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([
                ':username' => $this->username,
            ]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            $this->error = "Error fetching user: " . $e->getMessage();
            $result = null;
        }

        return $result;
    }

    public function authenticate(): bool {
        $this->error = null;

        $user = $this->get();
        if (!is_null($user) && password_verify($this->password, $user['password'])) {
            $this->id = $user['id'];
            return true;
        } else {
            $this->error = "Invalid username or password.";
            return false;
        }
    }

    public function update(?string $new_username, ?string $new_password): bool {
        $this->error = null;

        // Sanity check
        if (!isset($this->id)) {
            $this->error = "User ID is not set (unauthenticated).";
            return false;
        } elseif ($new_username === null && $new_password === null) {
            $this->error = "No new username or password provided.";
            return false;
        } elseif ($new_username !== null && !$this->validate_username()) {
            $this->error = "Invalid new username: " . $this->error;
            return false;
        }
        $new_password_hashed = $new_password !== null ? password_hash($new_password, PASSWORD_BCRYPT) : null;

        $query = <<<EOS
            UPDATE users
            SET 
                username = COALESCE(:new_username, username), 
                password = COALESCE(:new_password, password)
            WHERE id = :id;
        EOS;
        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([
                ':new_username' => $new_username,
                ':new_password' => $new_password_hashed,
                ':id' => $this->id
            ]);

            if ($stmt->rowCount() === 1) {
                $this->username = $new_username ?? $this->username;
                $this->password = $new_password_hashed ?? $this->password;
                return true;
            } else {
                $this->error = "Failed to update user {$this->username}: ";
                return false;
            }
        } catch (\PDOException $e) {
            $this->error = "Error updating user: " . $e->getMessage();
            return false;
        }
    }

    public function delete(): bool {
        $this->error = null;

        // Sanity check
        if (!isset($this->id)) {
            $this->error = "User ID is not set (unauthenticated).";
            return false;
        }

        $query = <<<EOS
            DELETE FROM users
            WHERE id = :id;
        EOS;
        $stmt = $this->db->prepare($query);

        try {
            return $stmt->execute([
                ':id' => $this->id,
            ]);
        } catch (\PDOException $e) {
            $this->error = "Error deleting user: " . $e->getMessage();
            return false;
        }
    }

    public function validate_username(): bool {
        $this->error = null;

        if (empty($this->username)) {
            $this->error = "Username cannot be empty.";
        } elseif (strlen($this->username) > 254) {
            $this->error = "Username must be no longer than 254 characters.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $this->username)) {
            $this->error = "Username can only contain letters, numbers, and underscores.";
        } else {
            return true;
        }

        return false;
    }

    public static function get_all(): array {
        $db = new Database();
        $query = <<<EOS
            SELECT id, username
            FROM users;
        EOS;
        $stmt = $db->connection->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_BOTH);
    }
}