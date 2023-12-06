<?php

namespace App\Config;

enum EtatExperimentation: int {
    case demandeInstallation = 0;
    case installee = 1;
    case demandeRetrait = 2;
    case retiree = 3;
}

?>
