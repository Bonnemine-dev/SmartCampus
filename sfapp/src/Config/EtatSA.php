<?php

namespace App\Config;

enum EtatSA: int {
    case eteint = 0;
    case marche = 1;
    case probleme = 2;
}

?>