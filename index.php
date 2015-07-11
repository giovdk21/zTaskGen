<?php
/**
 * Simple script to generate a CSV file combining the list of tasks with the list of subtasks.
 * The generated CSV file is meant to be used with Zoho Projects (http://www.zoho.com)
 *
 * @copyright Copyright (c) 2015, Giovanni Derks
 * @version   0.1
 * @license   https://github.com/giovdk21/zTaskGen/blob/master/LICENSE BSD 3-Clause License
 */

session_name('zTaskGen');
session_start();

// reset stored checkboxes values on POST submission
if (!empty($_POST['do_submit'])) {
    $_SESSION['prepend'] = (empty($_POST['prepend']) ? 0 : 1);
}

$tasks = getParam('tasks');
$subtasks = getParam('subtasks');
$prependTaskName = (int)getParam('prepend', 0);


/**
 * Get the value the requested parameter from POST or SESSION.
 * The parameter value is also stored into SESSION.
 *
 * @param string $name    The name of the parameter
 * @param string $default Default value if not set
 *
 * @return mixed
 */
function getParam($name, $default = "")
{
    $res = $default;

    if (isset($_POST[$name])) {
        $res = $_POST[$name];
        $_SESSION[$name] = $res;
    } elseif (isset($_SESSION[$name])) {
        $res = $_SESSION[$name];
    }

    return filter_var($res, FILTER_SANITIZE_STRING);
}

/**
 * Truncate string after the given maximum number of characters
 *
 * @param string $string
 * @param int    $max
 *
 * @return string
 */
function truncateStr($string, $max = 80)
{
    if (strlen($string) > $max) {
        $string = substr($string, 0, $max)."...";
    }
    return $string;
}


if (!empty($_POST['do_submit'])) {
    // Generate CSV File

    // Init the csvArray with the CSV headers
    $csvArray = array(array('Task name', 'Parent task'));

    $tasksArray = explode("\n", $tasks);
    $subtasksArray = explode("\n", $subtasks);

    foreach ($tasksArray as $task) {
        $task = trim($task);
        if (!empty($task)) {
            $csvArray[] = array($task, '');

            foreach ($subtasksArray as $subtask) {
                $subtask = trim($subtask);
                if (!empty($subtask)) {
                    $csvArray[] = array(
                        ($prependTaskName ? truncateStr($task).' - ' : '').$subtask,
                        $task,
                    );
                }
            }
        }
    }


    // Send CSV to download
    if (count($csvArray) > 1) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename='.'tasks.csv');

        $out = fopen('php://output', 'w');
        foreach ($csvArray as $csvLine) {
            fputcsv($out, $csvLine);
        }
        fclose($out);

        session_write_close();
        die();
    }
}
?><!DOCTYPE html>
<html>
<head>
    <!-- Standard Meta -->
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- Site Properities -->
    <title>Tasks Generator</title>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/semantic-ui/2.0.3/semantic.min.css">
</head>
<body>

<h1 class="ui center aligned header">Tasks Generator</h1>

<div class="ui container">
    <form method="post" action="index.php">
        <div class="ui form">
            <div class="two fields">
                <div class="field">
                    <label for="tasks">Tasks</label>
                    <textarea id="tasks" name="tasks"><?= $tasks; ?></textarea>
                </div>
                <div class="field">
                    <label for="subtasks">Subtasks</label>
                    <textarea id="subtasks" name="subtasks"><?= $subtasks; ?></textarea>
                </div>
            </div>
            <div class="field">
                <div class="ui checkbox">
                    <input type="checkbox"
                           id="prepend" name="prepend"
                           value="1"
                        <?= ($prependTaskName === 1 ? 'checked="checked"' : ''); ?>>
                    <label for="prepend">Prepend task name to subtasks</label>
                </div>
            </div>
            <input type="hidden" id="do_submit" name="do_submit" value="1">
            <input type="submit" class="ui submit button">
        </div>
    </form>
</div>


<style>
    h1.ui.center.header {
        margin-top: 2em;
    }
</style>

</body>
</htmL>
