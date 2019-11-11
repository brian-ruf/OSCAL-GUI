REM Set this variable to the location of your local PHP instance
set loc="c:\php\php.exe"

rem c:\php\php -S localhost:80
start cmd /c %loc% -S localhost:80
start http://localhost/
rem pause