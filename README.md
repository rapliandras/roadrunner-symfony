# roadrunner-symfony
Roadrunner app server configuration for symfony

**What does this package do?**
* has a working worker configuration that bridges PSR requests from Symfony to Roadrunner
* has all required dependencies installed
* enables debugging applications (web profiler works, phpstorm xdebug works, the symfony `dump()` function is overwritten to write objects to the roadrunner console)
* fixes internal server errors where trying to reach non-existing static files.

**Installation:**

 0. Make sure PHP CLI is installed and the roadrunner executable was compiled. For help, see RoadRunner docs at https://roadrunner.dev/docs/intro-install
 1. Make sure the roadrunner executable is in your `PATH`. If you named the executable different than rr or rr.exe, or didn't rewrite `PATH` for some reason, rewrite `rrserve.sh` after requiring the package accordingly.
 2. `composer require rapliandras/roadrunner-symfony`
 3. Set the APP_DIR directive in your environment. (`echo "APP_DIR="$(pwd) >> .env`)

**Use (Windows):**
From mingw32 (git bash): `./rrserve.sh` From command line or powershell terminal: `rrserve.exe`. Keep in mind that the Windows terminal will not care about the cool coloring of logged messages.

**Use (Linux):**
From terminal: `./rrserve.sh`

The application defaults to `localhost:8081`, you can change the port like this: `./rrserve.sh -o http.address=:80`

Special thanks to:
All the https://roadrunner.dev/ dev team and
https://github.com/dunglas who published the base of this code.
