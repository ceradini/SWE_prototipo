<?php defined('BASEPATH') OR exit('No direct script access allowed');

# Includes the autoloader for libraries installed with composer
require FCPATH . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Language\LanguageClient;

/**
 * @param $string string The text to analyze. Should be in UTF-8 encoding for avoid error
 * @param null $config array with a configuration for a LanguageClient Client object
 * @return string JSON with the full response from the API.
 */
function analyzeText($string, $config = null)
{
    $CI = &get_instance();
    $CI->config->load('google_cloud');

    $keyFilePath = $CI->config->item('key_file_path');

    $language = new LanguageClient([
        'keyFilePath' => $keyFilePath
    ]);

    if ($config === null)
        $config = loadDefaultConfig('it');

    return json_encode($language->annotateText($string, $config)->info());
}

/**
 * @param $language string  The language of the document. Both ISO and BCP-47 language codes are accepted.
 * @return array The configuration for a LanguageClient Client object that run entities, syntax and sentiment analysis
 */
function loadDefaultConfig($language)
{
    return [
        'features' => ['entities', 'syntax', 'sentiment'],
        'language' => $language,
        'type' => 'PLAIN_TEXT',
        'encodingType' => 'UTF8'
    ];
}

/**
 * @param $report result (as array) from analyzeText
 * @param $type type of sentence (positive,neutral,negative)
 * @return json of array of sentences (positive or negative) and array of sentimen result
 */
function get_sentences($report = array(), $type = 'positive')
{
    $sentences = array();
    $scores    = array();

    if(isset($report['sentences'])){
        foreach($report['sentences'] as $sentence){
            $score = $sentence['sentiment']['score'];

            if($type == 'positive'){
                if($score > 0.25){
                    $result[] = $sentence['text']['content'];
                    $scores[] = $score;
                }
            }
            else if($type == 'negative'){
                if($score < -0.25){
                    $result[] = $sentence['text']['content'];
                    $scores[] = $score;
                }
            }
            else {  // neutral
                if($score >= -0.25 && $score <= 0.25){
                    $result[] = $sentence['text']['content'];
                    $scores[] = $score;
                }
            }
        }
    }

    $result = array(
        'sentences' => $sentences,
        'scores'    => $scores
    );

    return json_encode($result);
}