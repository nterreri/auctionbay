<?php
/*form input validation functions,
  see http://www.lynda.com/MySQL-tutorials/Welcome/119003/136910-4.html*/

function fieldname_as_text($fieldname) {
  $fieldname = str_replace("_", " ", $fieldname);
  $fieldname = ucfirst($fieldname);
  return $fieldname;
}

// * presence
// use trim() so empty spaces don't count
// use === to avoid false positives
// empty() would consider "0" to be empty
function has_presence($value) {
	return isset($value) && $value !== "";
}

//  Modified from original to include boolean return value
function validate_presences($required_fields) {
  global $errors;
  $result = 1;

  foreach($required_fields as $field) {
    $value = trim($_POST[$field]);
  	if (!has_presence($value)) {
  		$errors[$field] = fieldname_as_text($field) . " can't be blank";
      $result = 0;
  	}
  }

  return $result;
}

// * string length
// max length
function has_max_length($value, $max) {
	return strlen($value) <= $max;
}

function validate_max_lengths($fields_with_max_lengths) {
	global $errors;
	// Expects an assoc. array
	foreach($fields_with_max_lengths as $field => $max) {
		$value = trim($_POST[$field]);
	  if (!has_max_length($value, $max)) {
	    $errors[$field] = fieldname_as_text($field) . " is too long, must be less than {$max} characters";
	  }
	}
}

/*  Constructs an appropriate error if the parameters do not match*/
function matches($first_field, $second_field) {
  global $errors;

  //strcmp returns 0 where the strings match, see http://php.net/manual/en/function.strcmp.php
  if(strcmp($first_field, $second_field)) {
    $errors['passwords'] = "Passwords do not match";
  }//else do nothing
}

/*  Constructs an appropriate error if the parameter */
function validate_phone($field) {
    global $errors;

    if(ctype_digit($_POST[$field])) {

      return 1;
    } else {
      $errors[$field] = fieldname_as_text($field) . " must be a series of digits";
      return 0;
    }
}

/* Checks that the email does not already exist within the database,
* preconditions: (1)the database can be queried from the point this function is
* called, and (2) there is an $errors global visible from the point this
* function is called*/
function validate_email($email) {
  global $errors;

  //$email value in the "email" column of the "user" table
  if(query_count_occurrences($email, "email", "user")) {
    //if it occurs one or more times
    $errors['email_used'] = "The email address has been used, please use another email address.";
  }
}

/*Utility function to reset the $errors global array*/
function clear_errors() {
  global $errors;

  $errors = array();
}

?>
