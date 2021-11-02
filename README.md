Parse your file by simply running this command:

`parser.php --file example_1.csv --unique-combinations=combination_count.csv`

Normally, to run a PHP script, you type ```php ./parser.php```, or more conveniently, ```php parser.php```.
To make a php script executable you need to add the script to your PATH. 
Your PATH is a set of directories which your shell environment looks in to find executable files.

What you can do is make a **php_shell** directory in your home and place your command line scripts in there or just **clone this project** to your _home directory_, then add that directory to your path. 

You can do all of these tasks by running the following commands:

`echo "PATH=\$HOME/php_shell:\$PATH" >> ~/.bashrc`

`source ~/.bashrc`


To run the _unit/integration_ test, you need to run the following command:

`composer install`

And after that just run the following command in the root of the project.

`./vendor/bin/phpunit ParserTest.php`

