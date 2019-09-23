<?php
ini_set('display_errors', 'stderr');

use App\Kernel;
use Spiral\Goridge\StreamRelay;
use Spiral\RoadRunner\PSR7Client;
use Spiral\RoadRunner\Worker;
use Spiral\Debug;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zend\Diactoros\ResponseFactory;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\StreamFactory;
use Zend\Diactoros\UploadedFileFactory;

require '../../autoload.php';

$staticAssetFolders = ["/media/"];

function dump($message)
{
    $dumper = new Debug\Dumper();
    $dumper->setRenderer(Debug\Dumper::ERROR_LOG, new Debug\Renderer\ConsoleRenderer());
    $dumper->dump($message, Debug\Dumper::ERROR_LOG);
}

// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV']) && !isset($_ENV['APP_ENV'])) {
    if (!class_exists(Dotenv::class)) {
        throw new \RuntimeException(
            'APP_ENV environment variable is not defined. You need to define environment variables for configuration or add "symfony/dotenv" as a Composer dependency to load variables from a .env file.'
        );
    }
    (new Dotenv(true))->load(__DIR__ . '/../../../.env');
}

$env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'dev';
$debug = (bool)($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? ('prod' !== $env));

if ($debug) {
    umask(0000);

    Symfony\Component\Debug\Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts(explode(',', $trustedHosts));
}

$kernel = new Kernel($env, $debug);
$relay = new StreamRelay(STDIN, STDOUT);
$psr7 = new PSR7Client(new Worker($relay));
$httpFoundationFactory = new HttpFoundationFactory();
$psrHttpFactory = new PsrHttpFactory(
    new ServerRequestFactory,
    new StreamFactory,
    new UploadedFileFactory,
    new ResponseFactory
);


function isAssetFolder($URI, $DIR, $ASSETFOLDERS)
{
    foreach($ASSETFOLDERS as $ASSETFOLDER){
        if(strstr($URI, $ASSETFOLDER)
            && !file_exists($DIR . $URI)){
            return true;
        }
    }
    return false;
}

while ($req = $psr7->acceptRequest()) {
    try {
        $request = $httpFoundationFactory->createRequest($req);

        if(isAssetFolder($request->getUri(), $_ENV["APP_DIR"], $staticAssetFolders)){
            $response = new Response();
            $response->setStatusCode(404);
            $psr7->respond($psrHttpFactory->createResponse($response));
        } else {
            $response = $kernel->handle($request);
            $psr7->respond($psrHttpFactory->createResponse($response));
        }

        $kernel->terminate($request, $response);
        $kernel->reboot(null);


    } catch (\Throwable $e) {
        $psr7->getWorker()->error((string)$e);
    }
}
