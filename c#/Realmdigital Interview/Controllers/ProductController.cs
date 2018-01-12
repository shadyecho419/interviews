using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Web;
using System.Web.Http;
using Newtonsoft.Json;

namespace Realmdigital_Interview.Controllers
{
    public class ProductController
    {
        private string response = "";
        private string baseURL = "http://192.168.0.241/eanlist?type=Web";

        [Route("product")]
        public string GetProductById(string productId)
        {
            string response = GetRequestResponse("id", productId);
            return JsonConvert.SerializeObject(GetProductPrice(response)); //convert result to json string and return
        }

        [Route("product/search")]
        public string GetProductsByName(string productName)
        {
            string response = GetRequestResponse("names", productName);
            return JsonConvert.SerializeObject(GetProductPrice(response)); //convert result to json string and return
        }
        
        /*
         *  moved request to one function using key as parm name and value as the value of the parm for the POST request
         */
        private string GetRequestResponse(string key, string value){
            try
            {
                using (var client = new WebClient())
                {
                    client.Headers[HttpRequestHeader.ContentType] = "application/json";
                    response = client.UploadString(baseURL, "POST", String.Format("{ \"{0}\": \"{1}\" }", key, value));
                }

            }
            catch(Exception ex)
            {
                Console.WriteLine("An error occured while trying POST request: {0}", ex);
            }

            return response != "" ? response : null;
        }
        
        /*
         * returning List<Object> is more uniform for procesing a singular result (using unique ID) 
         * as well as many results (using name for search)
         */
        private List<object> GetProductPrice(String response){
            var result = new List<object>();
            var prices = new List<object>(); // moved out to prevent instantiation of a new object in each loop run
            try
            {
                var reponseObject = JsonConvert.DeserializeObject<List<ApiResponseProduct>>(response);              
                for (int i = 0; i < reponseObject.Count; i++)
                {
                    prices = new List<object>();
                    for (int j = 0; j < reponseObject[i].PriceRecords.Count; j++)
                    {
                        if (reponseObject[i].PriceRecords[j].CurrencyCode == "ZAR")
                        {
                            prices.Add(new
                            {
                                Price = reponseObject[i].PriceRecords[j].SellingPrice,
                                Currency = reponseObject[i].PriceRecords[j].CurrencyCode
                            });
                        }
                    }

                    result.Add(new
                    {
                        Id = reponseObject[i].BarCode,
                        Name = reponseObject[i].ItemName,
                        Prices = prices
                    });
                    prices = null;
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("An error occured while getting product price: {0}", ex);
            }
            
            return result.Count > 0 ? result : null;
        }
    }

    class ApiResponseProduct
    {
        public string BarCode { get; set; }
        public string ItemName { get; set; }
        public List<ApiResponsePrice> PriceRecords { get; set; }
    }

    class ApiResponsePrice
    {
        public string SellingPrice { get; set; }
        public string CurrencyCode { get; set; }
    }
}