HTML PARSER
====================

## Environment
        - Apache server
        - Over php 7.1
        - Install paquettg/php-html-parser using composer
    
## Format of request
        - Method: "GET"
        - parameter:
            url = {URL} # Just put the url of staff page.
        
        
### Response

        - A array will be returned for infomation of stuff.
        - Ex
            On my local server : http://localhost/Scrapper/?url=https://www.burnschevyofgaffney.com/Staff
            Response :
                Array
                (
                    [0] => {"name":"Sam Burns","title":"Dealer","description":"I have grown up in the car business and other than four years in the U.S air force, it is the only profession I have known. I graduated from the Citadel in 1981 and was married the same year. I moved to Gaffney in 1990 to operate BURNS CHEVROLET. Terri and I have four children and four grandchildren. The Lord has blessed us! ","phone":"","email":"sam@burnsofgaffney.com","image":"https:\/\/media-dmg.assets-cdk.com\/teams\/repository\/export\/v\/3\/036\/ce0807f2d1005807200146edef087\/036ce0807f2d1005807200146edef087.jpg"}
                    [1] => {"name":"Rick Whetstone Sr.","title":"General Sales Manager","description":"Hometown : Shelby, NC Favorite Restaurant: Ruth Chris Steakhouse Hobbies: Family, Church, Working Out","phone":"","email":"rick@burnsofgaffney.com","image":"https:\/\/media-dmg.assets-cdk.com\/teams\/repository\/export\/v\/5\/fe9\/0346011d9100581f910145edef087\/fe90346011d9100581f910145edef087.jpg"}
                    [2] => {"name":"Scott Young","title":"Finance Manager","description":"Hometown : Fort Mill, SC Favorite Restaurant: Lady's Island Dockside Beaufort, SC Hobbies: Spending Time with Wife and 4 Kids","phone":"","email":"sales@burnsofgaffney.com","image":"https:\/\/media-dmg.assets-cdk.com\/teams\/repository\/export\/v\/3\/ab4\/bdea81b4d10058a6610145efa6b30\/ab4bdea81b4d10058a6610145efa6b30.jpg"}
                    ...
                )