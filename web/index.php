<?php

use JpnForPhp\Transliterator\Kana;
use JpnForPhp\Transliterator\Romaji;
use Symfony\Component\HttpFoundation\Request;

require('../vendor/autoload.php');

$app = new Silex\Application();

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => 'php://stderr',
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.twig');
});

$app->get('/transliterator', function () use ($app) {
    return $app['twig']->render('transliterator.twig');
});

$app->post('/transliterator/romaji', function (Request $request) use ($app) {
    $hepburn = new Romaji();
    $original = $request->get('string');
    if (empty($original)) {
        return $app['twig']->render('transliterator.twig', array(
            'error' => 'String cannot be empty'
        ));
    } else {
        return $app['twig']->render('romaji.twig', array(
            'original' => $original,
            'hepburn' => $hepburn->transliterate($original),
        ));
    }
});

$app->post('/transliterator/kana', function (Request $request) use ($app) {
    $hiragana = new Kana();
    $katakana = new Kana('katakana');
    $original = $request->get('string');
    if (empty($original)) {
        return $app['twig']->render('transliterator.twig', array(
            'error' => 'String cannot be empty'
        ));
    } else {
        return $app['twig']->render('kana.twig', array(
            'original' => $original,
            'hiragana' => $hiragana->transliterate($original),
            'katakana' => $katakana->transliterate($original),
        ));
    }
});

$app->run();
