<?php
// Fonctions Access aux données
function selectClients(): array {
    return [
        [
            "nom" => "camara",
            "prenom" => "lena",
            "telephone" => "786648301",
            "adresse" => "mermoz"
        ],
        [
            "nom" => "camara1",
            "prenom" => "lena1",
            "telephone" => "786648302",
            "adresse" => "mermoz1"
        ]
    ];
}



function selectClientByTel(string $tel, array $clients): array|null {
    foreach ($clients as $client) {
        if ($client["telephone"] == $tel) {
            return $client;
        }
    }
    return null;
}

function insertClient(array &$clients, array $client): void {
    $clients[] = $client;
}

function estVide(string $value): bool {
    return empty($value);
}

function insertDette(array &$dettes, array $dette): void {
    $dettes[] = $dette;
}

function generateRef(array $dettes): string {
    return "DETTE" . (count($dettes) + 1);
}

function selectDetteByRef(string $ref, array $dettes): array|null {
    foreach ($dettes as $dette) {
        if ($dette["ref"] == $ref) {
            return $dette;
        }
    }
    return null; 
}


function upDette(array &$dettes, string $ref, float $montantVerse): bool {
    foreach ($dettes as &$dette) {
        if ($dette["ref"] == $ref) {
            $montantRestant = $dette["montant"] - $dette["montantVerse"];
            if ($montantVerse <= $montantRestant) {
                $dette["montantVerse"] += $montantVerse;
                return true; 
            } else {
                return false; 
            }
        }
    }
    return false; 
}

function afficheDettes(array $dettes): void {
    if (count($dettes) == 0) {
        echo "Aucune dette enregistrée.\n";
    } else {
        echo "\nListe des dettes :\n";
        foreach ($dettes as $dette) {
            echo "\n-----------------------------------------\n";
            echo "Référence : {$dette['ref']}\n";
            echo "Téléphone : {$dette['telephone']}\n";
            echo "Montant total : {$dette['montant']} F\n";
            echo "Montant payé : {$dette['montantVerse']} F\n";
            echo "Montant restant : " . ($dette['montant'] - $dette['montantVerse']) . " F\n";
            echo "Date : {$dette['date']}\n";
        }
        echo "\n-----------------------------------------\n";
    }
}


// Fonctions Services ou Métier
function uniqueTel(array $clients, string $sms): string {
    do {
        $value = readline($sms);
    } while (estVide($value) || selectClientByTel($value, $clients) != null);
    return $value;
}

function  enregistrerClient(array &$tabClients,array $client):bool{
    $result=  selectClientByTel($client["telephone"],$tabClients);
    if (  $result==null ) {
       insertClient($tabClients,$client);
       return true;
    }
    return false;
}


function enregistrerDetteM(array &$dettes, array $clients, string $telephone, float $montant): array|null {
    $client = selectClientByTel($telephone, $clients);
    if ($client == null) {
        return null; 
    }

    $ref = generateRef($dettes);
    $dette = [
        "ref" => $ref,
        "telephone" => $telephone,
        "montant" => $montant,
        "date" => date("Y-m-d"),
        "montantVerse" => 0
    ];

    insertDette($dettes, $dette);
    return $dette; 
}


function payerDette(array &$dettes, string $ref, float $montant): bool {
    $dette = selectDetteByRef($ref, $dettes);

    if ($dette == null) {
        return false; // Aucune dette trouvée
    }

    return upDette($dettes, $ref, $montant);
}



// Fonctions de présentation
function saisieClient(array $clients): array {
    return [
        "telephone" => uniqueTel($clients, "Entrez le numéro de téléphone : "),
        "nom" => saisieChampObligatoire("Entrez le nom : "),
        "prenom" => saisieChampObligatoire("Entrez le prénom : "),
        "adresse" => saisieChampObligatoire("Entrez l'adresse : "),
    ];
}

function saisieChampObligatoire(string $sms): string {
    do {
        $value = readline($sms);
    } while (estVide($value));
    return $value;
}


function enregistrerDetteP(array &$dettes, array $clients): void {
    $telephone = saisieChampObligatoire("Entrez le numéro de téléphone du client : ");
    $montant = (float)saisieChampObligatoire("Entrez le montant de la dette : ");

    $dette = enregistrerDetteM($dettes, $clients, $telephone, $montant);

    if ($dette === null) {
        echo "Aucun client trouvé pour ce numéro de téléphone. Veuillez réessayer\n";
        return;
    }

    echo "Dette enregistrée avec succès pour le client.\n";
    echo "Référence : {$dette['ref']}\n";
    echo "Montant : {$dette['montant']} F\n";
    echo "Date : {$dette['date']}\n";
}


function afficheClient(array $clients):void{
    if (count($clients)==0) {
        echo "Pas de client a affiche";
    }else {
        foreach ($clients as  $client) {
            echo"\n-----------------------------------------\n";
            echo "Telephone : ". $client["telephone"]."\t";
            echo "Nom : ". $client["nom"]."\t";
            echo "Prenom : ". $client["prenom"]."\t";
            echo "Adresse : ". $client["adresse"]."\t";
      }
    }
    
}


function payerDetteP(array &$dettes): void {
    $ref = saisieChampObligatoire("Entrez la référence de la dette : ");
    $montant = (float)saisieChampObligatoire("Entrez le montant à payer : ");

    if (payerDette($dettes, $ref, $montant)) {
        echo "Paiement effectué avec succès.\n";
    } else {
        echo "Paiement échoué. Vérifiez la référence ou le montant à payer.\n";
    }
}


// Fonction pour afficher le menu
function menu(): int {
    echo "\n****************** MENU ********************\n";
    echo "1. Ajouter un client\n";
    echo "2. Lister les clients\n";
    echo "3. Rechercher un client par téléphone\n";
    echo "4. Enregistrer une dette\n";
    echo "5. Lister les dettes\n";
    echo "6. Payer une dette\n";
    echo "7. Quitter\n";
    return (int)readline("Faites votre choix : ");
}

// Fonction principale
function principal() {
    $clients = selectClients();
    $choix = 0;
    $dettes = [];

    do {
        $choix = menu();
        switch ($choix) {
            case 1:
                $client = saisieClient($clients);
                if (enregistrerClient($clients, $client)) {
                    echo "Client enregistré avec succès.\n";
                } else {
                    echo "Ce numéro de téléphone existe déjà.\n";
                }
                break;

            case 2:
                afficheClient($clients);
                break;

            case 3:
                $tel = saisieChampObligatoire("Entrez le numéro de téléphone du client : ");
                $client = selectClientByTel($tel, $clients);
                if ($client == null) {
                    echo "Aucun client trouvé pour ce numéro.\n";
                } else {
                    echo "\nClient trouvé : \n";
                    echo "Nom : {$client['nom']}\n";
                    echo "Prénom : {$client['prenom']}\n";
                    echo "Téléphone : {$client['telephone']}\n";
                    echo "Adresse : {$client['adresse']}\n";
                }
                break;

            case 4:
                enregistrerDetteP($dettes, $clients);
                break;

            case 5:
                afficheDettes($dettes);
                break;

            case 6:
                payerDetteP($dettes);
                break;

            case 7:
                echo "Au revoir !\n";
                break;

            default:
                echo "Veuillez faire un choix valide.\n";
                break;
        }
    } while ($choix != 7);
}

principal();

