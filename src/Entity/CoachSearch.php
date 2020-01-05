<?php

namespace App\Entity;

class CoachSearch
{
   private $ville;
   
   private $sport;

   public function getVille(){
       return $this->ville;
   }

   public function setVille($ville){
       $this->ville = $ville;
       return $this;
   }

   public function getSport(){
       return $this->sport;
   }
   
   public function setSport($sport){
       $this->sport = $sport;
       return $this;
   }
   
}
