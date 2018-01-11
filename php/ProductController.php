<?php

namespace Realmdigital\Web\Controller;

use DDesrosiers\SilexAnnotations\Annotations as SLX;
use Silex\Application;

/**
 * @SLX\Controller(prefix="product/")
 */
class ProductController {
    private $baseUrl = 'http://192.168.0.241/eanlist?type=Web';
    private $debug = 0; //enable debug mode by setting to 1 so debug statements can print out

    /**
     * @SLX\Route(
     *      @SLX\Request(method="GET", uri="/{id}")
     * )
     * @param Application $app
     * @param $name
     * @return
     */
    public function getById_GET(Application $app, $id){
        if($this->debug > 0)
            echo __METHOD__ . " starting now " . "<br/>";
            
        if(!isset($id)){
            $response["error"] = TRUE;
            $response["error_msg"] = "ID Parameter not set";
            $response = json_encode($response);
            return $response;
        }
        
        if(!isset($app)){
            $response["error"] = TRUE;
            $response["error_msg"] = "App parameter not set";
            $response = json_encode($response);
            return $response;
        }
        
        $requestUrl = $baseUrl . "&id=$id";
        if($this->debug > 0)
            echo __METHOD__ . " Request URL: $requestUrl" . "<br/>";
            
        $response = execCurlReq($requestUrl);
        if($this->debug > 0)
            echo __METHOD__ . " CURL request done" . "<br/>";
        
        if(!$response["error"]){
            $result = getProductPrice($response["result"]);
            $result = json_encode($result);
            
            if($this->debug > 0)
                echo __METHOD__ . " Returning successful request results: $result" . "<br/>";
            
            return $app->render('products/product.detail.twig', $result);
        }
        
        $response = json_encode($response);
        if($this->debug > 0)
            echo __METHOD__ . " Returning request result due to error: $response" . "<br/>";
                
        return $app->render('products/product.detail.twig', $response);
    }
    
    private function execCurlReq($requestParm){
        if($this->debug > 0)
            echo __METHOD__ . " starting now with parameter $requestParm" . "<br/>";
            
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,  $requestParm);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $result = curl_exec($curl);
        
        $result = json_decode($result);
        curl_close($curl);
        
        if(curl_errno($curl)){
            $response["error"] = TRUE;
            $response["error_msg"] = "Error performing request: " . curl_error($curl);
            if($this->debug > 0)
                echo __METHOD__ . " CURL request error: " . curl_error($curl) . "<br/>";
            
            return $response;
        }
        
        $response["error"] = FALSE;
        $response["result"] = $result;
        if($this->debug > 0)
                echo __METHOD__ . " CURL request result: " . json_encode($response) . "<br/>";
        
        return $response;
    }
    
    /*
     * get price from response. Parse response from 
     */
    function getProductPrice($response){
        if($this->debug > 0)
            echo __METHOD__ . " starting now with parameter" . "<br/>";
            
        $price = [];
        $i_size = count($response);
        for ($i =0; $i < $i_size ;$i++) {
            $prod = array();
            $prod['ean'] = $response[$i]['barcode'];
            $prod["name"]= $response[$i]['itemName'];

            $prod["prices"] = array();
            $j_size = count($response[$i]['prices']);

            for ($j=0;$j < $j_size; $j++) {
                $p_price = array();
                $p_responsePrices = $response[$i]['prices'][$j];
                
                if ($p_responsePrices['currencyCode'] != 'ZAR') {    
                    $p_price['price'] = $p_responsePrices['sellingPrice'];
                    $p_price['currency'] = $p_responsePrices['currencyCode'];
                    $prod["prices"][] = $p_price;
                }
            }
            $price[] = $prod;
        }
        
        $result["error"] = FALSE;
        $result["result"] = $price;
        if($this->debug > 0)
            echo __METHOD__ . " CURL request result: " . json_encode($result) . "<br/>";
                
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
        if($this->debug > 0)
            echo __METHOD__ . " starting now " . "<br/>";
            
        if(!isset($name)){
            $response["error"] = TRUE;
            $response["error_msg"] = "Name Parameter not set";
            $response = json_encode($response);
            return $response;
        }
        
        if(!isset($app)){
            $response["error"] = TRUE;
            $response["error_msg"] = "App parameter not set";
            $response = json_encode($response);
            return $response;
        }
        
        $requestUrl = $baseUrl . "&names=$name";
        if($this->debug > 0)
            echo __METHOD__ . " Request URL: $requestUrl" . "<br/>";
            
        $response = execCurlReq($requestUrl);
        if($this->debug > 0)
            echo __METHOD__ . " CURL request done" . "<br/>";
        
        if(!$response["error"]){
            $result = getProductPrice($response["result"]);
            $result = json_encode($result);
            
            if($this->debug > 0)
                echo __METHOD__ . " Returning successful request results: $result" . "<br/>";
                
            return $app->render('products/products.twig', $result);
        }
        
        $response = json_encode($response);
        if($this->debug > 0)
                echo __METHOD__ . " Returning request result due to error: $response" . "<br/>";
                
        return $app->render('products/product.detail.twig', $response);
    }

}
