<?php

namespace Todo\models;

require_once(__DIR__ . '/../config/Database.php');
use Todo\config\Database;

class Todo {
    private \PDO $db;

    public string $id;
    public string $user_id;

    public string $title;
    public ?string $description;
    public bool $completed = false;
    public string $create_date {
        get {
            return date('Y-m-d h:i:s', strtotime($this->create_date));
        }
        set {
            $this->create_date = $value;
        }
    }
    public ?string $update_date {
        get {
            return date('Y-m-d h:i:s', strtotime($this->create_date));
        }
        set {
            $this->update_date = $value;
        }
    }
    public ?string $due_date {
        get {
            return date('Y-m-d', strtotime($this->create_date));
        }
        set {
            $this->due_date = $value;
        }
    }
    public ?string $error = null;

    public function __construct(
        string $user_id,
        string $title,
        ?string $description = null,
        ?bool $completed = false,
        ?string $due_date = null
    ) {
        $db = new Database();
        $this->db = $db->connection;

        $this->user_id = $user_id;
        $this->title = self::sanitize($title);
        $this->description = $description ? self::sanitize($description) : null;
        $this->completed = $completed;
        $this->due_date = $due_date;
    }

    public function create(): bool {
        $this->error = null;

        $query = <<<EOS
            INSERT INTO todos
                (user_id, title, description, completed, due_date)
            VALUES
                (:user_id, :title, :description, :completed, :due_date);
        EOS;
        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([
                ':user_id' => $this->user_id,
                ':title' => $this->title,
                ':description' => $this->description,
                ':completed' => (int)$this->completed,
                ':due_date' => $this->due_date
            ]);
            $this->id = $this->db->lastInsertId();
            return true;
        } catch (\PDOException $e) {
            $this->error = "Failed to create the todo entry: " . $e->getMessage();
            return false;
        }
    }

    static public function get_by_id(string $id): ?self {
        $db = new Database();
        $db = $db->connection;
        $query = <<<EOS
            SELECT *
            FROM todos
            WHERE id = :id
            LIMIT 1;
        EOS;
        $stmt = $db->prepare($query);

        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($result) {
            $todo = new self(
                $result['user_id'],
                $result['title'],
                $result['description'],
                (bool)$result['completed'],
                $result['due_date']
            );
            $todo->id = $result['id'];
            $todo->create_date = $result['create_date'];
            $todo->update_date = $result['update_date'];
            $todo->due_date = $result['due_date'];
            return $todo;
        } else {
            return null;
        }
    }

    public static function get_by_user_id(string $user_id): ?array {
        $db = new Database();
        $db = $db->connection;
        $query = <<<EOS
            SELECT *
            FROM todos
            WHERE user_id = :user_id;
        EOS;
        $stmt = $db->prepare($query);

        $stmt->execute([':user_id' => $user_id]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $todos = [];

        foreach ($results as $result) {
            $todo = new self(
                $user_id,
                $result['title'],
                $result['description'],
                (bool)$result['completed'],
                $result['due_date']
            );
            $todo->id = $result['id'];
            $todo->create_date = $result['create_date'];
            $todo->update_date = $result['update_date'];
            $todos[] = $todo;
        }

        return $todos;
    }

    public function update(?string $new_title, ?string $new_description, ?bool $new_completed, ?string $new_due_date): bool {
        $this->error = null;

        // Sanity check & sanitize
        if (!isset($this->id)) {
            $this->error = "Todo ID is required for update.";
            return false;
        } elseif (!isset($new_title) && !isset($new_description) && !isset($new_completed) && !isset($this->due_date)) {
            $this->error = "At least one field must be provided to update.";
            return false;
        }

        $new_title = $new_title ? self::sanitize($new_title) : null;
        $new_description = $new_description ? self::sanitize($new_description) : null;
        $new_completed = $new_completed ?: null;

        $query = <<<EOS
            UPDATE todos
            SET 
                title = COALESCE(:new_title, title), 
                description = COALESCE(:new_description, description), 
                completed = COALESCE(:new_completed, completed),
                due_date = COALESCE(:new_due_date, due_date)
            WHERE id = :id;
        EOS;
        $stmt = $this->db->prepare($query);

        try {
            $result = $stmt->execute([
                ':id' => (int)$this->id,
                ':new_title' => $new_title,
                ':new_description' => $new_description,
                ':new_completed' => $new_completed,
                ':new_due_date' => $new_due_date
            ]);

            // Update object
            if ($result && $this->reload() === true) {
                return true;
            } else {
                $this->error = "Failed to update todo entry.";
                return false;
            }
        } catch (\PDOException $e) {
            $this->error = "Failed to update the todo entry: " . $e->getMessage();
            return false;
        }
    }

    private function reload(): bool {
        $result = self::get_by_id($this->id);
        if (isset($result)) {
            $this->title = $result->title;
            $this->description = $result->description;
            $this->completed = $result->completed;
            $this->create_date = $result->create_date;
            $this->update_date = $result->update_date;
            $this->due_date = $result->due_date;
            return true;
        } else {
            return false;
        }
    }

    public function delete(): bool {
        $this->error = null;

//        // Sanity check
//        if (!isset($this->id)) {
//            $this->error = "Todo ID is required for deletion.";
//            return false;
//        }

        $query = <<<EOS
            DELETE FROM todos
            WHERE id = :id;
        EOS;
        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([':id' => (int)$this->id]);
            return true;
        } catch (\PDOException $e) {
            $this->error = "Failed to delete the todo entry: " . $e->getMessage();
            return false;
        }
    }

    public static function sanitize(string $input): string {
        return filter_var($input, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}