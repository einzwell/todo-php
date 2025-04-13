<?php

require_once(__DIR__ . '/../src/auth.php');
require_once(__DIR__ . '/../src/helper.php');
require_once(__DIR__ . '/../src/models/Todo.php');

use Todo\models\Todo;

session_start();
require_login();

$user_id = get_current_user_id();
$errors = [];
$success = '';
$pending_todos = [];
$completed_todos = [];

switch ($_POST['_method'] ?? $_SERVER['REQUEST_METHOD']) {
    case 'POST':
        if (!verify_csrf($_POST['csrf_token'])) {
            $errors[] = 'Invalid request.';
        } else {
            if (isset($_POST['_logout']) && $_POST['_logout'] == 'true') {
                unset($_SESSION);
                session_destroy();
                redirect('login.php', 'User logged out successfully.', 'success');
            } else {
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $completed = isset($_POST['completed']) && $_POST['completed'] === true;
                $due_date = $_POST['due_date'] ?: null;

                $todo = new Todo($user_id, $title, $description, $completed, $due_date);
                if ($todo->create()) {
                    $success = 'Todo created successfully.';
                    redirect('index.php', 'Todo created successfully.', 'success');
                } else {
                    $errors[] = $todo->error;
                }
            }
        }
        break;

    case 'GET':
        $result = Todo::get_by_user_id($user_id);

        $pending_todos = array_filter($result, function ($todo) {
            return !$todo->completed;
        });

        $completed_todos = array_filter($result, function ($todo) {
            return $todo->completed;
        });

        break;

    case 'PUT':
        $_PUT = $_POST;
        if (!verify_csrf($_POST['csrf_token'])) {
            $errors[] = 'Invalid request.';
        } else {
            $todo_id = $_PUT['todo_id'];
            $title = $_PUT['title'] ?: null;
            $description = $_PUT['description'] ?: null;
            $completed = isset($_PUT['completed']);
            $due_date = $_PUT['due_date'] ?: null;

            $todo = Todo::get_by_id($todo_id);
            if (isset($todo)) {
                if ($todo->update($title, $description, $completed, $due_date)) {
                    $success = 'Todo updated successfully.';
                    redirect('index.php', 'Todo updated successfully.', 'success');
                } else {
                    $errors[] = $todo->error;
                }
            } else {
                $errors[] = "Todo to be updated cannot be found";
            }
        }
        break;

    case 'DELETE':
        $_DELETE = $_POST;
        if (!verify_csrf($_DELETE['csrf_token'])) {
            $errors[] = 'Invalid request.';
        } else {
            $todo_id = $_DELETE['todo_id'];

            $todo = Todo::get_by_id($todo_id);
            if (isset($todo)) {
                if ($todo->delete()) {
                    $success = 'Todo deleted successfully.';
                    redirect('index.php', 'Todo deleted successfully.', 'success');
                } else {
                    $errors[] = $todo->error;
                }
            } else {
                $errors[] = "Todo to be deleted cannot be found.";
            }
        }
        break;

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Todo App</title>
    <link href="assets/styles.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
</head>

<body>
<?php require_once(__DIR__ . '/partials/navbar.php');  ?>

<div class="container">
    <div class="row mb-3 mt-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="text-truncate me-2">Welcome, <b><?= $_SESSION['username'] ?></b></h1>
                <button class="btn btn-primary flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createTodoModal">
                    <i class="fas fa-plus me-1"></i> Add New Todo
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php foreach ($errors as $error): ?>
                <p class="mb-0"><?php echo $error; ?></p>
            <?php endforeach; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <p class="mb-0"><?php echo $success; ?></p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Todo List -->
    <div class="row">
        <?php if (empty($completed_todos) && empty($pending_todos)): ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-list-check fa-5x text-muted mb-3"></i>
                        <h3>No Todos Yet</h3>
                        <p class="text-muted">You don't have any todos yet. Create one to get started!</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTodoModal">
                            <i class="fas fa-plus me-1"></i> Add New Todo
                        </button>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Pending Todos -->
            <h3 class="mt-4 mb-3">Pending (<?= count($pending_todos) ?>)</h3>
            <?php if (empty($pending_todos)): ?>
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted text-center mb-0">No pending tasks. Great job!</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php /* @var $todo_item Todo */ foreach ($pending_todos as $todo_item): ?>

                    <!-- Todo Card -->
                    <div class="col-md-5 mb-5">
                        <div class="card todo-card">
                            <!-- Header -->
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <!-- Title -->
                                <h5 class="card-title mb-0 me-2">
                                    <b class="text-truncate"><?php echo $todo_item->title; ?></b>
                                </h5>
                               <!-- Due Date Pill, Edit, and Complete buttons -->
                                <div class="d-flex justify-content-end align-items-center">
                                    <?php if ($todo_item->due_date): $timeout = time() > strtotime($todo_item->due_date) ?>
                                        <!-- Due Date Pill -->
                                        <div class="badge rounded-pill bg-<?= $timeout ? 'danger' : 'secondary' ?>">
                                            <small class="text-white">
                                                <i class="fas <?= $timeout ? 'fa-circle-xmark' : 'fa-clock' ?>"></i>
                                                <?php echo $todo_item->due_date; ?>
                                            </small>
                                        </div>
                                    <?php endif; ?>
                                    <!-- Edit button -->
                                    <button class="btn btn-sm btn-outline-secondary btn-icon me-2 ms-2"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editTodoModal<?php echo $todo_item->id; ?>">
                                        <span class="fas fa-edit"></span>
                                    </button>
                                    <!-- Complete Button -->
                                    <button class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#toggleStatusModal<?php echo $todo_item->id; ?>">
                                        <span class="fas fa-check"></span>
                                    </button>
                                </div>
                            </div>
                            <!-- Body -->
                            <div class="card-body">
                                <p class="card-text text-truncate">
                                    <?php echo !empty($todo_item->description) ? str_replace('\n', '', $todo_item->description) : '<span class="text-muted fst-italic">No description</span>'; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Todo Modal -->
                    <div class="modal fade" id="editTodoModal<?php echo $todo_item->id; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="index.php" method="post">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                                    <input type="hidden" name="todo_id" value="<?php echo $todo_item->id; ?>">

                                    <div class="modal-header">
                                        <h5 class="modal-title text-truncate">
                                            <b>Todo: <?php echo $todo_item->title ?></b></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="title<?php echo $todo_item->id; ?>"
                                                   class="form-label">Title</label>
                                            <input type="text" class="form-control"
                                                   id="title<?php echo $todo_item->id; ?>" name="title"
                                                   value="<?php echo $todo_item->title; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="description<?php echo $todo_item->id; ?>" class="form-label">Description</label>
                                            <textarea class="form-control" id="description<?php echo $todo_item->id; ?>"
                                                      name="description"
                                                      rows="3"><?php echo $todo_item->description; ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="due_date<?php echo $todo_item->id; ?>" class="form-label">Due
                                                Date</label>
                                            <input type="date" class="form-control"
                                                   id="due_date<?php echo $todo_item->id; ?>" name="due_date"
                                                   value="<?php echo $todo_item->due_date; ?>">
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                   value="true"
                                                   id="completed<?php echo $todo_item->id; ?>"
                                                   name="completed" <?php echo $todo_item->completed ? 'checked' : ''; ?>>
                                            <label class="form-check-label"
                                                   for="completed<?php echo $todo_item->id; ?>">Mark as completed</label>
                                        </div>
                                        <small class="text-muted d-block">
                                            <i class="fas fa-play me-1"></i>
                                            Created: <?php echo $todo_item->create_date ?? 'N/A'; ?>
                                        </small>
                                        <?php if (isset($todo_item->update_date)): ?>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-edit me-1"></i>
                                                Updated: <?php echo $todo_item->update_date; ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if (isset($todo_item->due_date)): ?>
                                            <small class="text-muted d-block">
                                                <?php echo $todo_item->completed ?
                                                    '<i class="fas fa-check-circle text-success me-1"></i>Completed' :
                                                    (time() > strtotime($todo_item->due_date) ?
                                                        '<i class="fas fa-circle-xmark me-1"></i> Past Due Date' :
                                                        '<i class="fas fa-clock me-1"></i> Pending'); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger me-auto" data-bs-toggle="modal"
                                                data-bs-target="#deleteTodoModal<?php echo $todo_item->id; ?>"
                                                data-bs-dismiss="modal">
                                            <span class="fas fa-trash-alt"></span> Delete
                                        </button>
                                        <button type="button" class="btn btn-light btn-secondary"
                                                data-bs-dismiss="modal">Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Todo Modal -->
                    <div class="modal fade" id="deleteTodoModal<?php echo $todo_item->id; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="index.php" method="post">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                                    <input type="hidden" name="todo_id" value="<?php echo $todo_item->id; ?>">

                                    <div class="modal-header">
                                        <h5 class="modal-title"><b>Delete Todo</b></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to delete this todo?</p>
                                        <p><strong><?php echo $todo_item->title; ?></strong></p>
                                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel
                                        </button>
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Status Modal -->
                    <div class="modal fade" id="toggleStatusModal<?php echo $todo_item->id; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="index.php" method="post">
                                    <input type="hidden" name="_method" value="PUT">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                                    <input type="hidden" name="todo_id" value="<?php echo $todo_item->id; ?>">
                                    <input type="hidden" name="title" value="<?php echo $todo_item->title; ?>">
                                    <input type="hidden" name="description"
                                           value="<?php echo $todo_item->description; ?>">
                                    <input type="hidden" name="completed"
                                           value="<?php echo $todo_item->completed ? "false" : "true"; ?>">
                                    <input type="hidden" name="due_date" value="<?php echo $todo_item->due_date; ?>">

                                    <div class="modal-header">
                                        <h5 class="modal-title"><b>Mark as Complete</b></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Are you sure you want to mark this todo as complete?</p>
                                        <p><strong><?php echo $todo_item->title; ?></strong></p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel
                                        </button>
                                        <button type="submit" class="btn btn-primary">Mark as Complete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Completed Todos (Collapsible) -->
            <div class="col-12 mt-4">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white" role="button" data-bs-toggle="collapse"
                         data-bs-target="#completedTasks">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0">Completed (<?= count($completed_todos) ?>)</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    <div id="completedTasks" class="collapse">
                        <div class="card-body">
                            <?php if (empty($completed_todos)): ?>
                                <p class="text-muted text-center mb-0">No completed tasks yet.</p>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($completed_todos as $todo_item): ?>

                                        <!-- Todo Card -->
                                        <div class="col-md-4 mb-3">
                                            <div class="card todo-card border-success">
                                                <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
                                                    <!-- Title -->
                                                    <h5 class="card-title mb-0 todo-completed text-truncate">
                                                        <b><?php echo $todo_item->title; ?></b>
                                                    </h5>
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <!-- Due Date Pill -->
                                                        <div class="badge rounded-pill bg-white">
                                                            <small class="text-success">
                                                                <i class="fas fa-check-circle"></i>
                                                                <?php echo date('Y-m-d', strtotime($todo_item->update_date)); ?>
                                                            </small>
                                                        </div>
                                                        <!-- Edit button -->
                                                        <button class="btn btn-sm btn-outline-light btn-icon ms-2"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editTodoModal<?php echo $todo_item->id; ?>">
                                                            <span class="fas fa-edit"></span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <p class="card-text text-truncate todo-completed">
                                                        <?php echo !empty($todo_item->description) ? nl2br($todo_item->description) : '<span class="text-muted fst-italic">No description</span>'; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Edit Todo Modal -->
                                        <div class="modal fade" id="editTodoModal<?php echo $todo_item->id; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="index.php" method="post">
                                                        <input type="hidden" name="_method" value="PUT">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                                                        <input type="hidden" name="todo_id" value="<?php echo $todo_item->id; ?>">

                                                        <div class="modal-header">
                                                            <h5 class="modal-title text-truncate">
                                                                <b>Todo: <?php echo $todo_item->title ?></b></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="title<?php echo $todo_item->id; ?>"
                                                                       class="form-label">Title</label>
                                                                <input type="text" class="form-control"
                                                                       id="title<?php echo $todo_item->id; ?>" name="title"
                                                                       value="<?php echo $todo_item->title; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="description<?php echo $todo_item->id; ?>" class="form-label">Description</label>
                                                                <textarea class="form-control" id="description<?php echo $todo_item->id; ?>"
                                                                          name="description"
                                                                          rows="3"><?php echo $todo_item->description; ?></textarea>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="due_date<?php echo $todo_item->id; ?>" class="form-label">Due
                                                                    Date</label>
                                                                <input type="date" class="form-control"
                                                                       id="due_date<?php echo $todo_item->id; ?>" name="due_date"
                                                                       value="<?php echo $todo_item->due_date; ?>">
                                                            </div>
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input"
                                                                       value="true"
                                                                       id="completed<?php echo $todo_item->id; ?>"
                                                                       name="completed" <?php echo $todo_item->completed ? 'checked' : ''; ?>>
                                                                <label class="form-check-label"
                                                                       for="completed<?php echo $todo_item->id; ?>">Mark as completed</label>
                                                            </div>
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-play me-1"></i>
                                                                Created: <?php echo $todo_item->create_date ?? 'N/A'; ?>
                                                            </small>
                                                            <small class="text-muted d-block">
                                                                <i class="fas fa-edit me-1"></i>
                                                                Updated: <?php echo $todo_item->update_date; ?>
                                                            </small>
                                                            <small class="text-muted d-block">
                                                                <?php echo $todo_item->completed ?
                                                                    '<i class="fas fa-check-circle text-success me-1"></i>Completed' :
                                                                    (time() > strtotime($todo_item->due_date) ?
                                                                        '<i class="fas fa-circle-xmark me-1"></i> Past Due Date' :
                                                                        '<i class="fas fa-clock me-1"></i> Pending'); ?>
                                                            </small>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-danger me-auto" data-bs-toggle="modal"
                                                                    data-bs-target="#deleteTodoModal<?php echo $todo_item->id; ?>"
                                                                    data-bs-dismiss="modal">
                                                                <span class="fas fa-trash-alt"></span> Delete
                                                            </button>
                                                            <button type="button" class="btn btn-light btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Todo Modal -->
                                        <div class="modal fade" id="deleteTodoModal<?php echo $todo_item->id; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="index.php" method="post">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                                                        <input type="hidden" name="todo_id" value="<?php echo $todo_item->id; ?>">

                                                        <div class="modal-header">
                                                            <h5 class="modal-title"><b>Delete Todo</b></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete this todo?</p>
                                                            <p><strong><?php echo $todo_item->title; ?></strong></p>
                                                            <p class="text-danger"><small>This action cannot be undone.</small></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel
                                                            </button>
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Toggle Status Modal -->
                                        <div class="modal fade" id="toggleStatusModal<?php echo $todo_item->id; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="index.php" method="post">
                                                        <input type="hidden" name="_method" value="PUT">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                                                        <input type="hidden" name="todo_id" value="<?php echo $todo_item->id; ?>">
                                                        <input type="hidden" name="title" value="<?php echo $todo_item->title; ?>">
                                                        <input type="hidden" name="description"
                                                               value="<?php echo $todo_item->description; ?>">
                                                        <input type="hidden" name="completed"
                                                               value="<?php echo $todo_item->completed ? "false" : "true"; ?>">
                                                        <input type="hidden" name="due_date" value="<?php echo $todo_item->due_date; ?>">

                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                <?php echo $todo_item->completed ? 'Mark as Incomplete' : 'Mark as Complete'; ?>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to mark this todo
                                                                as <?php echo $todo_item->completed ? 'incomplete' : 'complete'; ?>?</p>
                                                            <p><strong><?php echo $todo_item->title; ?></strong></p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel
                                                            </button>
                                                            <button type="submit"
                                                                    class="btn <?php echo $todo_item->completed ? 'btn-warning' : 'btn-success'; ?>">
                                                                <?php echo $todo_item->completed ? 'Mark as Incomplete' : 'Mark as Complete'; ?>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Create Todo Modal -->
<div class="modal fade" id="createTodoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <form action="index.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                <input type="hidden" name="action" value="create">

                <div class="modal-header">
                    <h5 class="modal-title"><b>Add New Todo</b></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="due_date" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Todo</button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once(__DIR__ . '/partials/footer.php'); ?>

<script>
    // Auto-dismiss alerts after 10 seconds
    $(document).ready(function () {
        setTimeout(function () {
            $('.alert').alert('close');
        }, 10 * 1000);

        // Handle cancel button in ANY delete modal
        document.querySelectorAll('.modal[id^="deleteTodoModal"] .btn-secondary').forEach(button => {
            button.addEventListener('click', function () {
                // Get the source modal ID from data attribute
                const sourceModalId = document.body.getAttribute('data-current-modal');

                // Wait for delete modal to close
                setTimeout(() => {
                    if (sourceModalId && sourceModalId.startsWith('editTodoModal')) {
                        // Reopen the edit modal
                        const editModal = document.querySelector('#' + sourceModalId);
                        if (editModal) {
                            new bootstrap.Modal(editModal).show();
                        }
                    }
                }, 150);
            });
        });
    });
</script>
</body>