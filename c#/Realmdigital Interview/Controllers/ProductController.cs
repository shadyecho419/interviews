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
        private string RequestResponse = "";
        private const string baseURL = "http://192.168.0.241/eanlist?type=Web";
        private List<object> ProductPriceResult = new List<object>();

        [Route("product")]
        public string GetProductById(string ProductId)
        {
            if(ProductId.Trim() != "")
            {
                string RequestResponse = GetRequestResponse("id", ProductId);
                return JsonConvert.SerializeObject(GetProductPrice(RequestResponse)); //convert result to json string and return
            }

            ProductPriceResult.Add(new
            {
                Success = false,
                Message = "Invalid ID parameter set for request"
            });

            return JsonConvert.SerializeObject(ProductPriceResult);
        }

        [Route("product/search")]
        public string GetProductsByName(string ProductName)
        {
            if (ProductName.Trim() != "")
            {
                string RequestResponse = GetRequestResponse("names", ProductName);
                return JsonConvert.SerializeObject(GetProductPrice(RequestResponse)); //convert result to json string and return
            }

            ProductPriceResult.Add(new
            {
                Success = false,
                Message = "Invalid Name parameter set for request"
            });

            return JsonConvert.SerializeObject(ProductPriceResult);
        }
        
        /*
         *  moved request to one function using Key as parm name and Value as the Value of the parm for the POST request
         */
        private string GetRequestResponse(string Key, string Value){
            try
            {
                using (var client = new WebClient())
                {
                    client.Headers[HttpRequestHeader.ContentType] = "application/json";
                    RequestResponse = client.UploadString(baseURL, "POST", String.Format("{ \"{0}\": \"{1}\" }", Key, Value));
                }

            }
            catch(Exception ex)
            {
                Console.WriteLine("An error occured while trying POST request: {0}", ex);
            }

            return RequestResponse != "" ? RequestResponse : null;
        }
        
        /*
         * returning List<Object> is more uniform for procesing a singular result (using unique ID) 
         * as well as many results (using name for search)
         */
        private List<object> GetProductPrice(String RequestResponse){         
            var ProductPrice = new List<object>(); // moved out to prevent instantiation of a new object in each loop run
            try
            {
                var ReponseObject = JsonConvert.DeserializeObject<List<ApiRequestResponseProduct>>(RequestResponse);              
                for (int i = 0; i < ReponseObject.Count; i++)
                {
                    ProductPrice = new List<object>();
                    for (int j = 0; j < ReponseObject[i].PriceRecords.Count; j++)
                    {
                        if (ReponseObject[i].PriceRecords[j].CurrencyCode == "ZAR")
                        {
                            ProductPrice.Add(new
                            {
                                Price = ReponseObject[i].PriceRecords[j].SellingPrice,
                                Currency = ReponseObject[i].PriceRecords[j].CurrencyCode
                            });
                        }
                    }

                    ProductPriceResult.Add(new
                    {
                        Success = true,
                        Id = ReponseObject[i].BarCode,
                        Name = ReponseObject[i].ItemName,
                        Prices = ProductPrice
                    });
                    ProductPrice = null;
                }
            }
            catch (Exception ex)
            {
                Console.WriteLine("An error occured while getting product price: {0}", ex);
            }

            if(ProductPriceResult.Count < 0)
            {
                ProductPriceResult.Add(new
                {
                    Success = false,
                    Message = "No price for requested item"
                });
            }
            
            return ProductPriceResult;
        }
    }

    class ApiRequestResponseProduct
    {
        public string BarCode { get; set; }
        public string ItemName { get; set; }
        public List<ApiRequestResponsePrice> PriceRecords { get; set; }
    }

    class ApiRequestResponsePrice
    {
        public string SellingPrice { get; set; }
        public string CurrencyCode { get; set; }
    }
}