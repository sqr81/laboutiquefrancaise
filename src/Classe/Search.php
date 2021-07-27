<?php

namespace App\Classe;

use App\Entity\Category;

class Search

{
    //pour la recherche par nom en public pour eviter de devoir faire des getters et setters
    /**
     * @var string
     */
    public $string ='';
    //pour la recherche par categorie
    /**
     * @var Category[]
     */
    public $categories = [];

    //on evite d utiliser le make form de la console parcequ on a pas d entites à lier
}