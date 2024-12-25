<?php


class USER{
    protected $matricule;
    protected $nom;
    protected $prenom;
    protected $email;
    private $password;
    protected $tel;
    protected $post;


    public $array_post=['membre','administration'];


    public function verification_post($post){
        if(in_array($array_post,$post)){
            echo 'good';
        } else{
            echo 'error';
        }
    }

    // getter 
    public function getMatricule(){
        return $this->matricule;
    }

    public function getNom(){
        return $this->prenom;
    }

    public function getEmail(){
        return $this->email;
    }

    







  

    


}




?>