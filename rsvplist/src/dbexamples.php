<?php

// How to establish a connection object to
// the active Drupal database (set in settings.php)

// Approach 1:
$database = \Drupal::database();

// Approach 2:
$database = \Drupal::service('database');


// For historical and technical reasons, the type returned by
// \Drupal::database() is of type \Drupal\Core\Database\Connection
// As such you may come across code similar to this:

// Approach 3:
use Drupal\Core\Database\Database;

$database =  Database::getConnection();

// Note all three approaches will set $database as a connection object
// to the active Drupal database!



// Static query example
$database = \Drupal::database();
$query = $database->query("SELECT [id], [example] FROM {cooltable}");
$result = $query->fetchAll();

// The typical structure of a dynamic query will look something like:

// Note ‘et’ is the abbreviation for referencing 'exampletable'.
$query = $database->OPERATION('exampletable', 'et');
$query->condition('et.field_name', 'value', 'condition');
$query->fields('et', ['field_name1', 'field_name2', …]);
$query->OTHER_POSSIBLE_OPERATIONS();



// Static query
$database = \Drupal::database();
$result = $database->query("SELECT uid, name, created FROM {users} u
                            WHERE uid <> 0 LIMIT 50 OFFSET 0");

// Dynamic query
$database = \Drupal::database();
$query = $database->select('users', 'u');

// Add extra detail to this query object: a condition, fields and a range.
$query->condition('u.uid', 0, '<>');
$query->fields('u', ['uid', 'name', 'created']);
$query->range(0, 50);

$result = $query->execute();


// Simplified Dynamic query
$database = \Drupal::database();
$query = $database->select('users', 'u');

// Simplified query.
$query->condition('u.uid', 0, '<>')
      ->fields('u', ['uid', 'name', 'created'])
      ->range(0, 50);

$result = $query->execute();

$query = \Drupal::database()->insert('rsvplist');

try {



  $query = \Drupal::database()->insert('rsvplist');

  // Specify the fields that the query will insert into.
  $query->fields([
    'mail' => 'example@example.com',
    'nid' => 8,
    'uid' => 1,
    'created' => \Drupal::time()->getRequestTime(),
  ]);

  $query->execute();


}
catch (Exception $e) {

}



$query = \Drupal::database()->update('rsvplist');


$query = \Drupal::database()->update('rsvplist');
// Update the mail of the record having a nid of 8 and uid of 1.
$query->fields([
    'mail' => 'real.name@example.com',
  ])
  ->condition('nid', 8, '=')
  ->condition('uid', 1, '=')
  ->condition('mail', 'example@example.com', '=');

$query->execute();

$database->merge('rsvplist');

try {

  $database = \Drupal::database();
  // Update a record with a key of 123 if it exists,
  // else insert the record.
  $database->merge('rsvplist')
    ->key('id', 123)  // An example of a primary key and its value.
    ->fields([
      'nid' => 8,
      'uid' => 1,
      'mail' => 'druplicon@example.com',
    ])
    ->execute();


}
catch (Exception $e) {

}
$database->delete('rsvplist');


// Delete all the RSVPs of user id 1
$num_deleted = $database->delete('rsvplist')
  ->condition('uid', 1, '=')
  ->execute(); // execute() returns the number of records deleted.
$record++;


$result = $query->execute();
foreach ($result as $record) {
  // Do something with each $record object.
  // E.g. Display the name of every person in the result set.
  \Drupal::messenger()->addMessage(t("@person is attending.",
                                      ['@person' => $record->name]));
}




$result = $query->execute();

$all = $result->fetchAll(); // An array containing all of the result set rows.
$all_col_1 = $result->fetchCol(1); //  The index of the column number to fetch.

for ($i = 0; $i < $result->rowCount(); $i++) {
  $row = $result->fetchAssoc(); //  The row as an array, rather than an object.
  print "$i row has values of:" . print_r($row, TRUE);
}



try {
  $query = \Drupal::database()->insert('rsvplist');

  // Specify the fields that the query will insert into.
  $query->fields([
    'mail' => 'example@example.com',
    'nid' => 8,
    'uid' => 1,
    'created' => \Drupal::time()->getRequestTime(),
  ]);

  $query->execute();
}
catch (\Exception $e) {
  Drupal::messenger()->addError(t("Unable to save the record at this time."));
}


