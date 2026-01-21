<?php // Template Name: Download

use App\OnBase;
use Timber\Timber;

$context = Timber::context();

$context['post'] = Timber::get_post();

try {
    $onbase = new OnBase();
    $context['documents'] = $onbase->getDocuments();
    //dump($context['documents']);

} catch (Exception $e) {
    echo $e->getMessage();
}

Timber::render('pages/download.twig', $context);
