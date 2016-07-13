<?php
/* Sincroniza uma base de dados e gera um dicionário de dados dela.
   por: Carlson Albert Soares
   data: 2011-01-31
   Ultimo update: 2015-11-30
*/


      class DataDictionary extends GojiraCore{
         
         private $conn;  //Conexão com o banco de daods
         
         
          
         //Construtores do objeto
         function __construct1( $conn ){

            $this->conn = $conn;

         }
         
         //Sincroniza as informações de uma base de dados para gerar o dicionario de dada dela
         function syncDb( $dbName ){
         
            $sql   = 'SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = "' . $dbName . '"';
            $query = $this->conn->query( $sql );
            
            while( $row = $this->conn->fetch( $query ) ){
               $this->syncTable( $dbName, $row['TABLE_NAME'] );
            }
         
         }
         
         
         
         //Sincronza as informações de uma tabela da base de dados
         function syncTable( $dbName, $tableName ){
            
            $sql = 'SELECT COLUMN_NAME,
                           ORDINAL_POSITION,
                           COLUMN_DEFAULT,
                           IS_NULLABLE,
                           DATA_TYPE,
                           CHARACTER_MAXIMUM_LENGTH,
                           NUMERIC_PRECISION,
                           NUMERIC_SCALE,
                           COLUMN_KEY,
                           EXTRA,
                           COLUMN_COMMENT
                       FROM information_schema.COLUMNS 
                           WHERE TABLE_SCHEMA = "' . $dbName . '"
                             AND TABLE_NAME = "' . $tableName . '"';
            
            $query = $this->conn->query( $sql );
            
            while( $row = $this->conn->fetch( $query ) ){
            }
            
         }
         
         
         
      }



      //Objeto de erro personalizadp
      class DataDictionaryException extends Exception{
         
         function __construct( $message, $err_cod ){
            parent::__construct( $message, $err_cod );
         }
         
      }

