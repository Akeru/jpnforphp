<?php

use JpnForPhp\Analyzer\Analyzer;
use JpnForPhp\Converter\Converter;
use JpnForPhp\Helper\Helper;
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

$app->get('/analyzer', function () use ($app) {
    return $app['twig']->render('analyzer.twig');
});

$app->post('/analyzer', function (Request $request) use ($app) {
    $string = $request->get('string');
    if (empty($string)) {
        return $app['twig']->render('analyzer.twig', array(
            'error' => 'String cannot be empty'
        ));
    } else {
        return $app['twig']->render('analyzer-result.twig', array(
            'string' => $string,
            'countHiragana' => Analyzer::countHiragana($string),
            'countKanji' => Analyzer::countKanji($string),
            'countKatakana' => Analyzer::countKatakana($string),
            'hasHiragana' => Analyzer::hasHiragana($string),
            'hasKanji' => Analyzer::hasKanji($string),
            'hasJapaneseLetters' => Analyzer::hasJapaneseLetters($string),
            'hasJapanesePunctuationMarks' => Analyzer::hasJapanesePunctuationMarks($string),
            'hasJapaneseWritings' => Analyzer::hasJapaneseWritings($string),
            'hasKana' => Analyzer::hasKana($string),
            'hasKatakana' => Analyzer::hasKatakana($string),
            'length' => Analyzer::length($string),
        ));
    }
});

$app->get('/helper', function () use ($app) {
    return $app['twig']->render('helper.twig');
});

$app->post('/helper', function (Request $request) use ($app) {
    $string = $request->get('string');
    if (empty($string)) {
        return $app['twig']->render('helper.twig', array(
            'error' => 'String cannot be empty'
        ));
    } else {
        return $app['twig']->render('helper-result.twig', array(
            'string' => $string,
            'katakana' => Helper::convertHiraganaToKatakana($string),
            'hiragana' => Helper::convertKatakanaToHiragana($string),
            'extractedHiragana' => Helper::extractHiragana($string),
            'extractedKatakana' => Helper::extractKatakana($string),
            'extractedKana' => Helper::extractKana($string),
            'extractedKanji' => Helper::extractKanji($string),
            'removedMacron' => Helper::removeMacrons($string),
            'split' => Helper::split($string),
        ));
    }
});

$app->get('/transliterator', function () use ($app) {
    return $app['twig']->render('transliterator.twig');
});

$app->post('/transliterator/romaji', function (Request $request) use ($app) {
    $original = $request->get('string');
    if (empty($original)) {
        return $app['twig']->render('transliterator.twig', array(
            'error' => 'String cannot be empty'
        ));
    } else {
        $hepburn = new Romaji();
        return $app['twig']->render('transliterator-romaji.twig', array(
            'original' => $original,
            'hepburn' => $hepburn->transliterate($original),
        ));
    }
});

$app->post('/transliterator/kana', function (Request $request) use ($app) {
    $original = $request->get('string');
    if (empty($original)) {
        return $app['twig']->render('transliterator.twig', array(
            'error' => 'String cannot be empty'
        ));
    } else {
        $hiragana = new Kana();
        $katakana = new Kana('katakana');
        return $app['twig']->render('transliterator-kana.twig', array(
            'original' => $original,
            'hiragana' => $hiragana->transliterate($original),
            'katakana' => $katakana->transliterate($original),
        ));
    }
});


$app->get('/converter', function () use ($app) {
    return $app['twig']->render('converter.twig');
});

$app->post('/converter/numeral/japanese', function (Request $request) use ($app) {
    $numeral = $request->get('numeral');
    if (empty($numeral)) {
        return $app['twig']->render('converter.twig', array(
            'error' => 'Numeral cannot be empty'
        ));
    } else {
        $hiragana = new Kana();
        $romaji = Converter::toJapaneseNumeral($numeral, Converter::NUMERAL_READING);
        return $app['twig']->render('converter-numeral-japanese.twig', array(
            'numeral' => $numeral,
            'kanji' => Converter::toJapaneseNumeral($numeral),
            'hiragana' => $hiragana->transliterate($romaji, Kana::STRIP_WHITESPACE_ALL),
            'romaji' => $romaji,
        ));
    }
});

$app->post('/converter/year/japanese', function (Request $request) use ($app) {
    $year = $request->get('year');
    if (empty($year)) {
        return $app['twig']->render('converter.twig', array(
            'error' => 'Year cannot be empty'
        ));
    } else {
        try {
            $result = Converter::toJapaneseYear($year);
            $kana = Converter::toJapaneseYear($year, Converter::YEAR_KANA);
            $romaji = Converter::toJapaneseYear($year, Converter::YEAR_ROMAJI);
            return $app['twig']->render('converter-year-japanese.twig', array(
                'year' => $year,
                'result' => $result,
                'kana' => $kana,
                'romaji' => $romaji,
            ));
        } catch (Exception $e) {
            return $app['twig']->render('converter.twig', array(
                'error' => $e->getMessage()
            ));
        }
    }
});

$app->post('/converter/year/western', function (Request $request) use ($app) {
    $year = $request->get('year');
    if (empty($year)) {
        return $app['twig']->render('converter.twig', array(
            'error' => 'Year cannot be empty'
        ));
    } else {
        try {
            $result = Converter::toWesternYear($year);
            return $app['twig']->render('converter-year-western.twig', array(
                'year' => $year,
                'result' => $result,
            ));
        } catch (Exception $e) {
            return $app['twig']->render('converter.twig', array(
                'error' => $e->getMessage()
            ));
        }
    }
});

$app->run();
