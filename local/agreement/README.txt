# Agreement 
## Functionality
- Agreement form with a popup when a teacher login in our system
- Agreement form submit the faculty member only one time
- Faculty if try to open form again then view the message
- Admin can display the list of faculty  who agree with us
- Admin can display the list of faculty  who pending agree with us

##Uses
- Fist Step: Install this plugin
- Second Step:To find the place where you need to use the agreement popup form, just paste the following code  require_once("../local/agreement/check_user_type.php");

##notes:
- These plugins only apply to the Teacher's Rules, once a teacher has filled out an agreement form, the pop-up form will no longer be open to him, he will be able to work as before, and other users will be able to work normally.
- If this plugin is uninstalled, you must remove all hard coded (require_once("../local/agreement/check_user_type.php");) that has been used, otherwise the application will show an error.