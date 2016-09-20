<?php

/**
 * Amazonへのアクセスに関するユーティリティをまとめるユーティリティクラス
 * @author wataru
 */
class AmazonUtil {
    /**
     * Amazon Web Service ECommerces ServiceへのアクセスURLを生成します
     * http://webservices.amazon.co.jp/scratchpad/index.html
     * @param type $isbn
     * @return string
     */
    public static function createURL($isbn){
            $config = parse_ini_file("account.properties",true);

            // Your AWS Access Key ID, as taken from the AWS Your Account page
            $aws_access_key_id = $config['amazon']['AccessKeyId'];
            // Your AWS Secret Key corresponding to the above ID, as taken from the AWS Your Account page
            $aws_secret_key = $config['amazon']['SecretKey'];

            $associate_tag = $config['amazon']['AssociateTag'];

            // The region you are interested in
            $endpoint = "webservices.amazon.co.jp";

            $uri = "/onca/xml";

            $params = array(
                    "Service" => "AWSECommerceService",
                    "Operation" => "ItemLookup",
                    "AWSAccessKeyId" => $aws_access_key_id ,
                    "AssociateTag" => $associate_tag ,
                    "ResponseGroup" => "Large",
                    "ItemId" => $isbn ,
                    "SearchIndex" => "Books",
                    "IdType" => "ISBN"
            );

            // Set current timestamp if not set
            if (!isset($params["Timestamp"])) {
                    $params["Timestamp"] = gmdate('Y-m-d\TH:i:s\Z');
            }

            // Sort the parameters by key
            ksort($params);

            $pairs = array();

            foreach ($params as $key => $value) {
                    array_push($pairs, rawurlencode($key)."=".rawurlencode($value));
            }

            // Generate the canonical query
            $canonical_query_string = join("&", $pairs);

            // Generate the string to be signed
            $string_to_sign = "GET\n".$endpoint."\n".$uri."\n".$canonical_query_string;

            // Generate the signature required by the Product Advertising API
            $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $aws_secret_key, true));

            // Generate the signed URL
            $request_url = 'http://'.$endpoint.$uri.'?'.$canonical_query_string.'&Signature='.rawurlencode($signature);

            return $request_url;
    }
    /**
     * Amazonからの応答を書籍情報に変換します
     * @param type $parsed_xml
     * @return \Book
     */
    public static function castResultToBook($parsed_xml){
        require_once("Book.php");
        require_once("Author.php");
        
        $Item       = $parsed_xml->Items->Item;
        $Attributes = $Item->ItemAttributes;
        $book = new Book();
        $book->isbn = (string)$Attributes->ISBN;
        $book->asin = (string)$Item->ASIN;
        $book->ean = (string)$Attributes->EAN;
        $book->title = (string)$Attributes->Title;
        $book->image_url = (string)$Item->MediumImage->URL;
        $book->pubdate = (string)$Attributes->PublicationDate;
        $book->price = (string)$Attributes->ListPrice->Amount;
        $book->pages = (string)$Attributes->NumberOfPages;
        $book->publisher = (string)$Attributes->Publisher;
        $book->binding = (string)$Attributes->Binding;
        $book->currency_cd = (string)$Attributes->ListPrice->CurrencyCode;

        $author_names = array();
        foreach( $Attributes->Author as $author_name ) {
                $author_names[] = $author_name;
        }
        foreach( $Attributes->Creator as $creator ) {
                $author_names[] = $creator;
        }

        $author_array = array();
        for ( $i=0; $i<sizeof($author_names); $i++ ) {
                $author = new Author();
                $author->author = (string)($author_names[$i]);
                $author->role = (string)($author_names[$i]['Role']);
                $author_array[] = $author;
        }

        $book->authors = $author_array;

        return $book;
    }

}
