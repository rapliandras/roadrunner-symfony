# roadrunner-symfony
Roadrunner app server configuration for symfony

What does this package do?
* has a working worker configuration that bridges PSR requests from Symfony to Roadrunner
* has all required dependencies installed
* enables debugging applications (web profiler works, phpstorm xdebug works, the symfony `dump()` function is overwritten to write objects to the roadrunner console)
* fixes internal server errors where trying to reach non-existing static files.

Installation:
 1. Make sure the roadrunner executable is in your `PATH`. If you named the executable different than rr or rr.exe, rewrite `rrserve.sh` after requiring the package.
 2. `composer require rapliandras/roadrunner-symfony`

Use (Windows):
From mingw32 (git bash): `./rrserve.sh`

The application defaults to `localhost:8081`, you can change the port like this: `./rrserve.sh -o http.address=:80`

Special thanks to:
All the https://roadrunner.dev/ dev team and
https://github.com/dunglas who published the base of this code.
