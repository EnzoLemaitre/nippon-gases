<?php

/**
 * Template Name: Download Page
 * Description: Page de téléchargement des documents OnBase
 */

use Timber\Timber;
use App\OnBase;


// Détection de la langue avec Polylang
$current_lang = function_exists('pll_current_language') ? pll_current_language() : 'es';



// Mapping langue => code pays pour l'API OnBase
$lang_to_country = [

 'es' => 'ESP',
 'pt' => 'PRT',
 'fr' => 'FRA',
 'en' => 'GBR',
 'da' => 'DNK',
 'be' => 'BEL',
];

$country_code = isset($lang_to_country[$current_lang]) ? $lang_to_country[$current_lang] : 'ESP';

error_log("=== DEBUG ONBASE DOWNLOAD ===");
error_log("Langue détectée: " . $current_lang);
error_log("Code pays API: " . $country_code);

// Récupération des paramètres de recherche
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$document_type = isset($_GET['doc_type']) ? sanitize_text_field($_GET['doc_type']) : '';
$language_filter = isset($_GET['language']) ? sanitize_text_field($_GET['language']) : '';
$category_filter = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

$documents = [];

$error_message = null;

// Fonction helper pour les traductions
function get_translation($string) {
 return function_exists('pll__') ? pll__($string) : $string;
}

// Types de documents disponibles (traduits via Polylang)
$available_document_types = [

 'PDM - Policies' => get_translation('Policies'),
 'PDM - Certifications' => get_translation('Certifications'),
 'PDM - General Terms GTCS' => get_translation('General Terms'),
 'PDM - Instructions For Use' => get_translation('Instructions For Use'),
 'PDM - Privacy Indications' => get_translation('Privacy Indications'),
 'PDM - Risk Evaluation Sheets' => get_translation('Risk Evaluation Sheets'),
 'PDM - Safety Data Sheets' => get_translation('Safety Data Sheets')
];


// Langues disponibles par pays (hardcodées)
$languages_by_country = [

 'ESP' => [
  'es-ES' => 'Español',
  'en-GB' => 'English'
 ],

 'PRT' => [

  'pt-PT' => 'Português',
  'en-GB' => 'English'

 ],

 'FRA' => [

  'fr-FR' => 'Français',
  'en-GB' => 'English'

 ],

 'GBR' => [

  'en-GB' => 'English'

 ],

 'DNK' => [

  'da-DK' => 'Dansk',
  'en-GB' => 'English'

 ],

 'BEL' => [

  'nl-NL' => 'Nederlands',
  'fr-FR' => 'Français',
  'en-GB' => 'English'

 ]

];



// Catégories disponibles (corrigées selon l'API OnBase)
$available_categories = [

 'Quality' => get_translation('Quality'),
 'Environment' => get_translation('Environment'),
 'Safety' => get_translation('Safety'),
 'Finance' => get_translation('Finance'),
 'Legal' => get_translation('Legal'),
 'Human Resources' => get_translation('Human Resources'),
 'Food Safety' => get_translation('Food Safety'),
 'Information Security' => get_translation('Information Security')

];



// Langues disponibles pour le pays actuel
$available_languages = isset($languages_by_country[$country_code]) ? $languages_by_country[$country_code] : [];

try {

 // Vérifier qu'un type de document est sélectionné

 if (empty($document_type)) {
  error_log("Aucun type de document sélectionné - pas d'appel API");
  $documents = [];
 } else {

  // Configuration de l'API

  $client_id = $_ENV['ONBASE_CLIENT_ID'] ?? '';
  $client_secret = $_ENV['ONBASE_CLIENT_SECRET'] ?? '';
  $scope = $_ENV['ONBASE_SCOPE'] ?? '';

  if (empty($client_id) || empty($client_secret) || empty($scope)) {

   throw new Exception("Missing OnBase credentials in .env file");

  }


  // 1. Obtenir le token OAuth
  $token_url = "https://login.microsoftonline.com/152d35f3-e46a-4e14-b95c-f462dab4ce70/oauth2/v2.0/token";

  $token_data = [

   "grant_type" => "client_credentials",
   "client_id" => $client_id,
   "client_secret" => $client_secret,
   "scope" => $scope,

  ];


  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $token_url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  $token_response = curl_exec($ch);
  $token_json = json_decode($token_response, true);

  if (!isset($token_json["access_token"])) {

   error_log("Erreur obtention token: " . $token_response);

   throw new Exception("Unable to get access token");

  }



  $access_token = $token_json["access_token"];

  error_log("Token OAuth obtenu avec succès");



  // 2. Construire les filtres pour l'API

  $filters = [

   [

    "Operator" => "=",

    "Relation" => "AND",

    "Key" => "NG - Is Public",

    "Value" => "YES"

   ],

   [

    "Operator" => "=",

    "Relation" => "AND",

    "Key" => "NG - Country Code",

    "Value" => $country_code

   ]

  ];



  // 3. Appel API POST

  $api_url = "https://mule-worker-internal-ng-e-documents.de-c1.cloudhub.io:8082/api/v1/cms/public-doc-list";



  $payload = [

   "DocumentType" => $document_type,

   "Filters" => $filters,

   "Sorting" => [

    "KeywordName" => "NG - Original File Name",

    "OrderType" => "ASC"

   ]

  ];



  error_log("Payload API: " . json_encode($payload));



  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, $api_url);

  curl_setopt($ch, CURLOPT_POST, true);

  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

  curl_setopt($ch, CURLOPT_HTTPHEADER, [

   "Authorization: Bearer {$access_token}",

   "Content-Type: application/json"

  ]);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);



  $response = curl_exec($ch);

  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  curl_close($ch);



  error_log("Code HTTP: " . $http_code);

  error_log("Réponse API (200 premiers caractères): " . substr($response, 0, 200));



  if ($http_code === 200) {

   $documents = json_decode($response, true);



   if (!is_array($documents)) {

    error_log("Réponse n'est pas un tableau: " . $response);

    $documents = [];

   } else {

    error_log("Nombre de documents récupérés: " . count($documents));

     

    // FILTRAGE CÔTÉ PHP - Catégorie

    if (!empty($category_filter)) {

     error_log("Filtrage catégorie pour: " . $category_filter);

     $documents = array_filter($documents, function($doc) use ($category_filter) {

      if (!isset($doc['keywords']) || !is_array($doc['keywords'])) {

       return false;

      }

      foreach ($doc['keywords'] as $keyword) {

       if ($keyword['Key'] === 'NG - Category Name' && $keyword['Value'] === $category_filter) {

        return true;

       }

      }

      return false;

     });

     $documents = array_values($documents);

     error_log("Nombre de documents après filtrage catégorie: " . count($documents));

    }

     

    // FILTRAGE CÔTÉ PHP - Langue

    if (!empty($language_filter)) {

     error_log("Filtrage langue pour: " . $language_filter);

     $documents = array_filter($documents, function($doc) use ($language_filter) {

      if (!isset($doc['keywords']) || !is_array($doc['keywords'])) {

       return false;

      }

      foreach ($doc['keywords'] as $keyword) {

       if ($keyword['Key'] === 'NG - Language ISO Code' && $keyword['Value'] === $language_filter) {

        return true;

       }

      }

      return false;

     });

     $documents = array_values($documents);

     error_log("Nombre de documents après filtrage langue: " . count($documents));

    }

     

    // FILTRAGE CÔTÉ PHP - Recherche par nom

    if (!empty($search_query)) {

     error_log("Filtrage recherche pour: " . $search_query);

     $documents = array_filter($documents, function($doc) use ($search_query) {

      $name = $doc['Name'] ?? '';

      $match = stripos($name, $search_query) !== false;

      if ($match) {

       error_log("Match trouvé: " . $name);

      }

      return $match;

     });

     $documents = array_values($documents);

     error_log("Nombre de documents après filtrage recherche: " . count($documents));

    }

   }

  } else {

   error_log("Erreur API HTTP " . $http_code . ": " . $response);

   throw new Exception("API error (HTTP " . $http_code . ")");

  }

 }



} catch (Exception $e) {

 error_log("Exception OnBase: " . $e->getMessage());

 $error_message = $e->getMessage();

}



// Pagination

$items_per_page = 20;

$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

$total_items = count($documents);

$total_pages = ceil($total_items / $items_per_page);

$offset = ($current_page - 1) * $items_per_page;

$paged_documents = array_slice($documents, $offset, $items_per_page);



// Ajouter les URLs de téléchargement

foreach ($paged_documents as &$doc) {

 if (isset($doc['dochandle'])) {

  $doc['download_url'] = add_query_arg([

   'action' => 'download_onbase_document',

   'doc_id' => $doc['dochandle']

  ], admin_url('admin-ajax.php'));

 }

}

unset($doc);



// Préparation du contexte Timber

$context = Timber::context();

$context['post'] = Timber::get_post();



// Titre de page depuis ACF (champ download existant)

$download_meta = get_field('download');

$context['page_title'] = !empty($download_meta['title']) ? $download_meta['title'] : get_translation('What do you want to download?');



// Traductions

$context['i18n'] = [

 'choose_doc_type' => get_translation('Choose a document type'),

 'choose_language' => get_translation('Choose a language'),

 'choose_category' => get_translation('Choose a category'),

 'all_languages' => get_translation('All languages'),

 'all_categories' => get_translation('All categories'),

 'search' => get_translation('Search'),

 'search_placeholder' => get_translation('Search a document, a solution category...'),

 'all_results' => get_translation('All results'),

 'select_type_msg' => get_translation('Please select a document type to view available documents.'),

 'no_results' => get_translation('No documents found matching your criteria.')

];



$context['downloads'] = $paged_documents;

$context['available_doc_types'] = $available_document_types;

$context['available_languages'] = $available_languages;

$context['available_categories'] = $available_categories;

$context['filters'] = [

 'search' => $search_query,

 'doc_type' => $document_type,

 'language' => $language_filter,

 'category' => $category_filter

];

$context['pagination'] = [

 'count' => $total_items,

 'total' => $total_pages,

 'current' => $current_page

];

$context['results_count'] = $total_items;

$context['error_message'] = $error_message;

$context['country_code'] = $country_code;



// Rendu du template Twig

Timber::render('pages/download.twig', $context);
