<?php
/**
 * Viacheslav Rodionov
 * viacheslav@rodionov.top
 * Date: 11.09.2022
 * Time: 0:06В
 */

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Models\CustomFieldsValues\NumericCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\NumericCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\NumericCustomFieldValueModel;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Dotenv\Dotenv;

include_once '../vendor/autoload.php';

if (!isset($_GET['product_name']) || !isset($_GET['product_price'])) {
    exit('INVALID REQUEST');
}

$dotenv = new Dotenv;
$dotenv->load('../.env');

$apiClient = new AmoCRMApiClient(
    $_ENV['CLIENT_ID'], $_ENV['CLIENT_SECRET'], $_ENV['CLIENT_REDIRECT_URI']
);

$apiClient->setAccountBaseDomain($_ENV['ACCOUNT_DOMAIN']);

$rawToken = json_decode(file_get_contents('../token.json'), 1);
$token = new AccessToken($rawToken);

$apiClient->setAccessToken($token);

$productName = $_GET['product_name'];
$leadName = "Новая сделка $productName";
$price = +$_GET['product_price'];
$marginality = $price / 2;


$lead = (new LeadModel)->setName("Новая сделка {$_GET['product_name']}")
    ->setPrice($price)->setCustomFieldsValues(
        (new CustomFieldsValuesCollection)->add(
                (new TextCustomFieldValuesModel)->setFieldId(
                        $_ENV['PRODUCT_NAME_FIELD_ID']
                    )->setValues(
                        (new TextCustomFieldValueCollection)->add(
                                (new TextCustomFieldValueModel)->setValue(
                                    $productName
                                )
                            )
                    )
            )->add(
                (new NumericCustomFieldValuesModel)->setFieldId(
                        $_ENV['MARGINALITY_FIELD_ID']
                    )->setValues(
                        (new NumericCustomFieldValueCollection)->add(
                                (new NumericCustomFieldValueModel)->setValue(
                                    $marginality
                                )
                            )
                    )
            )
    );

$lead = $apiClient->leads()->addOne($lead);

echo "OK. LEAD_ID: {$lead->getId()}";

