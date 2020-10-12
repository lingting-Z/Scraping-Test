<?php

class DB{
    
    private $dbh;
    function __construct(){
		
        // Connect to local MySQL database
		$db="mysql:host=localhost;dbname=mydb";
		$user = "root";
		$pw = "";

        try{
			
            $this->dbh = new PDO($db,$user,$pw);
            echo "Connected database successfully";
			
        }catch(PDOException $pe){
			
            echo $pe->getMessage();
            die("bad database");
            
        }
        
    }
	//Insert post into posts table
	function addPost($category, $title, $link, $author, $publish_date, $content){
		
        try{
			
			// Prepared statement
            $stmt = $this->dbh->prepare("insert into posts (category, title, link, author, publish_date, content) 
										VALUES (:category, :title, :link, :author, :publish_date, :content)");
            
            return $stmt->execute(array("category"=>$category,"title"=>$title,"link"=>$link,"author"=>$author,"publish_date"=>$publish_date,"content"=>$content));
           
            
        }catch(PDOException $e){
            
            echo $e->getMessage();
			return false;
			
        }

    }
}
?>