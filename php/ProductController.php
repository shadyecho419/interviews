<?php

namespace Realmdigital\Web\Controller;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;

/**
 * @SLX\Controller(prefix="product/")
 */
class ProductController {
    private $url = 'http://192.168.0.241/eanlist?type=Web';
    /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/{id}")
     * )
     * @param Application $app
     * @param $name
     * @return
     */
    public function getById_GET(Application $app, $id){
        
        $requestData = array();
        $requestData['id'] = $id;
        $response = execCurlReq($requestData);
        /*$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,  $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);*/
        /*$result = [];
        for ($i =0; $i < count($response) ;$i++) {
            $prod = array();
            $prod['ean'] =$response[$i]['barcode']; //space discrepancy 
            $prod["name"]=$response[$i]['itemName'];  //space discrepancy 
            $prod["prices"] = array();
            for ($j=0;$j < count($response[$i]['prices']); $j++) {
                if ($response[$i]['prices'][$j]['currencyCode'] != 'ZAR') {
                    $p_price = array();
                    $p_price['price'] = $response[$i]['prices'][$j]['sellingPrice'];
                    $p_price['curreny'] = $response[$i]['prices'][$j]['currencyCode']; //mispelt p_price key
                    $prod["prices"][] = $p_price;
                }
            }
            $result[] = $prod;
        }*/
        getProductPrice($response);
        return $app->render('products/product.detail.twig', $result);
    }
    
    private function execCurlReq($requestParm){
        //change request to use http GET
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,  $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestParm);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);
        return $response;
    }
    
    private function getProductPrice($respose){
        $result = [];
        for ($i =0; $i < count($response) ;$i++) {
            $prod = array();
            $prod['ean'] = $response[$i]['barcode'];
            $prod["name"]= $response[$i]['itemName'];
            $prod["prices"] = array();
            for ($j=0;$j < count($response[$i]['prices']); $j++) {
                if ($response[$i]['prices'][$j]['currencyCode'] != 'ZAR') {
                    $p_price = array();
                    $p_price['price'] = $response[$i]['prices'][$j]['sellingPrice'];
                    $p_price['currency'] = $response[$i]['prices'][$j]['currencyCode'];
                    $prod["prices"][] = $p_price;
                }
            }
            $result[] = $prod;
        }
        return $result;
    }

    /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/search/{name}")
     * )
     * @param Application $app
     * @param $name
     * @return
     */
    public function getByName_GET(Application $app, $name){
        
        $requestData = array();
        $requestData['names'] = $name;
        $response = execCurlReq($requestData);
        /*$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,  'http://192.168.0.241/eanlist?type=Web');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $requestData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $response = json_decode($response);
        curl_close($curl);*/
        /*$result = [];
        for ($i =0; $i < count($response) ;$i++) {
            $prod = array();
            $prod['ean'] = $response[$i]['barcode'];
            $prod["name"]= $response[$i]['itemName'];
            $prod["prices"] = array();
            for ($j=0;$j < count($response[$i]['prices']); $j++) {
                if ($response[$i]['prices'][$j]['currencyCode'] != 'ZAR') {
                    $p_price = array();
                    $p_price['price'] = $response[$i]['prices'][$j]['sellingPrice'];
                    $p_price['currency'] = $response[$i]['prices'][$j]['currencyCode'];
                    $prod["prices"][] = $p_price;
                }
            }
            $result[] = $prod;
        }*/
        getProductPrice($response);
        return $app->render('products/products.twig', $result);
    }

}
