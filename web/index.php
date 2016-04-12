<?php

use Symfony\Component\HttpFoundation\Request;
use Domotique\Repository\GpioRepository;
use Domotique\Repository\TempRepository;
use \DateTime;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$gpioRepo = new GpioRepository();
$tempRepo = new TempRepository('database.db');

$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app->register(new Silex\Provider\SessionServiceProvider());



$app->get('/', function() use ($app, $gpioRepo, $tempRepo){
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $relays = $gpioRepo->findAllGpio();
    $sensors = $tempRepo->findAllSensors();
    $temps = [];

    foreach ($sensors as $sensor) {
        $temp = $tempRepo->findLastTemps($sensor['id'], 1, 1);
        $temps[] = [
            'name' => $sensor['name'],
            'date' => $temp[0]['date'],
            'temp' => $temp[0]['temp']
        ];
    }


    return $app['twig']->render('dashboard.twig', [
        'relays' => $relays,
        'temps' => $temps
    ]);
});

/*
########################################## JSON Temperatures
*/

$app->get('/temps/{modulo}', function($modulo) use ($app, $tempRepo){
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $data = [];
    $sensors = $tempRepo->findAllSensors();

    foreach ($sensors as $sensor) {
        $temps = $tempRepo->findLastTemps($sensor['id'], 720, $modulo);
        $formattedTemps = [];

        foreach ($temps as $temp) {
            $date = new DateTime($temp['date']);
            $formattedTemps[] = [$date->getTimestamp() * 1000, floatval($temp['temp'])];
        }
        $data[] = [
            'type' => 'area',
            'name' => $sensor['name'],
            'data' => $formattedTemps
        ];
    }

    return $app->json($data);
});

/*
########################################## Login
*/

$app->get('/login', function () use ($app) {
    if ($app['session']->get('user') != null) {
        return $app->redirect('/');
    }

    return $app['twig']->render('login.twig');
});

$app->post('/login', function() use ($app) {
    $username = $app['request']->get('username');
    $password = $app['request']->get('password');

    if ($username == null || $password == null) {
        return $app['twig']->render('login.twig');
    }

    if ($username != 'antoine' || $password != 'test') {
        return $app['twig']->render('login.twig', array(
            'error' => 'Identifiant ou Mot de passe incorrect'
        ));
    }

    $app['session']->set('user', array('username' => $username));

    return $app->redirect('/');
});

/*
########################################## Relay Configuration
*/

$app->get('/config/relay', function () use ($app, $gpioRepo) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $relays = $gpioRepo->findAllGpio();

    return $app['twig']->render('config/relay.twig', [
        'relays' => $relays
    ]);
});

$app->post('/config/relay/new', function (Request $request) use ($app, $gpioRepo) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $pin = $request->get('pin');
    $name = $request->get('name');

    $gpioRepo->newGpio($pin, $name);

    return $app->redirect('/config/relay');
});

$app->post('/config/relay/edit', function (Request $request) use ($app, $gpioRepo) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $id = $request->get('id');
    $pin = $request->get('pin');
    $name = $request->get('name');

    $gpioRepo->updateGpio($id, $pin, $name);

    return $app->redirect('/config/relay');
});

$app->get('/config/relay/delete/{id}', function ($id) use ($app, $gpioRepo) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $gpioRepo->deleteGpio($id);

    return $app->redirect('/config/relay');
});

/*
########################################## Temperature sensors Configuration
*/

$app->get('/config/temps', function () use ($app, $tempRepo) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $sensors = $tempRepo->findAllSensors();

    return $app['twig']->render('config/temps.twig', [
        'sensors' => $sensors
    ]);
});

$app->post('/config/temps/new', function (Request $request) use ($app, $tempRepo) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $device = $request->get('device');
    $name = $request->get('name');

    $tempRepo->newSensor($device, $name);

    return $app->redirect('/config/temps');
});

$app->post('/config/temps/edit', function (Request $request) use ($app, $tempRepo) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $id = $request->get('id');
    $device = $request->get('device');
    $name = $request->get('name');

    $tempRepo->updateSensor($id, $device, $name);

    return $app->redirect('/config/temps');
});

$app->get('/config/temps/delete/{id}', function ($id) use ($app, $tempRepo) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    $tempRepo->deleteSensor($id);

    return $app->redirect('/config/temps');
});

/*
########################################## Scenario Configuration
*/

$app->get('/config/scenario', function () use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    return $app['twig']->render('config/scenario.twig');
});


/*
########################################## Settings
*/

$app->get('/settings', function () use ($app) {
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    return $app['twig']->render('settings.twig');
});

/*
########################################## Disconnect
*/

$app->get('/disconnect', function () use ($app) {
    $app['session']->set('user', null);

    return $app->redirect('/');
});


/*
########################################## phpinfo
*/

$app->get('/phpinfo', function() {
    return phpinfo();
});

$app->run();
