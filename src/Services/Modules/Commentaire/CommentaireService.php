<?php

namespace Services\Modules\Commentaire;

use Facades\Modules\Module;
use Repositories\BalanceRepository;
use Repositories\BilanRepository;
use Repositories\DetailPosteRepository;
use Repositories\PosteRepository;
use Services\AbstractService;
use Services\ModuleService;
use Services\BalanceService;
use Traits\Modules\Commentaire\CommentaireLibreTrait;
use Traits\Modules\Commentaire\CommentaireStructureTrait;
use Traits\Modules\Commentaire\CommentaireTableTrait;

class CommentaireService extends AbstractService
{

    use CommentaireLibreTrait, CommentaireStructureTrait, CommentaireTableTrait;

    private BilanRepository $bilanRepository;

    private DetailPosteRepository $detailPosteRepository;

    private PosteRepository $posteRepository;

    private BalanceRepository $balanceRepository;

    private BalanceService $balanceService;


    public function __construct(BilanRepository $bilanRepository, DetailPosteRepository $detailPosteRepository, PosteRepository $posteRepository, BalanceRepository $balanceRepository, BalanceService $balanceService)
    {
        $this->bilanRepository = $bilanRepository;
        $this->detailPosteRepository = $detailPosteRepository;
        $this->posteRepository = $posteRepository;
        $this->balanceRepository = $balanceRepository;
        $this->balanceService = $balanceService;
    }

    /**
     * Check if the "COMMENTAIRE" module is enabled.
     *
     * @return bool Returns true if the "COMMENTAIRE" module is enabled, otherwise false.
     */
    public function isModuleEnable(): bool
    {
        return Module::isModuleEnable(ModuleService::COMMENTAIRE);
    }

    public function getSpecificities($slug): array
    {
        $return = [];

        switch ($slug) {
            case "resultat":
                $return = [
                    "type" => "liste",
                    "function" => $this->getResultatCommentaire()
                ];
                break;

            case "ecartReelPrevu":
                $return = [
                    "type" => "table",
                    "function" => $this->getEcartReelPrevuCommentaire()
                ];
                break;

            case "remunerationChargesGerants":
                $return = [
                    "type" => "table",
                    "function" => $this->getEcartReelPrevuMasseSalarialeCommentaire([
                        516 => "Rémunération gérants",
                        517 => "Charges sociales sur rémunération des gérants"
                    ]),
                    "commentaires" => $this->getCommentaireByFamille([516, 517], "poste", false),
                    "typeCommentaires" => "Charges"
                ];
                break;

            case "remunerationChargesSalaries":
                $return = [
                    "type" => "table",
                    "function" => $this->getEcartReelPrevuMasseSalarialeCommentaire([
                        518 => "Salaires bruts employés",
                        519 => "Charges sociales salariés"
                    ]),
                    "commentaires" => $this->getCommentaireByFamille([518, 519], "poste", false),
                    "typeCommentaires" => "Charges"
                ];
                break;

            case "achatNonStockes":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Achats non stockés", "poste"),
                    "typeCommentaires" => "Charges"
                ];
                break;

            case "chargesExternes":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Charges externes", "poste"),
                    "typeCommentaires" => "Charges"
                ];
                break;

            case "impotsEtTaxes":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Impôts et taxes", "poste"),
                    "typeCommentaires" => "Charges"
                ];
                break;
            
            case "impotsSociete":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Impôts société", "poste"),
                    "typeCommentaires" => "Charges"
                ];
                break;

            case "chargesFinancieres":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Charges financières", "poste"),
                    "typeCommentaires"
                ];
                break;

            case "chargesExceptionnelles":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Charges exceptionnelles", "poste"),
                    "typeCommentaires" => "Charges"
                ];
                break;

            case "amortissementsEtProvisions":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Dotations amort. et provisions", "poste"),
                    "typeCommentaires" => "Charges"
                ];
                break;

                case "resultatAnterieur":
                    $return = [
                        "commentaires" => $this->getCommentaireByFamille("Résultat Antérieur", "poste"),
                        "typeCommentaires" => "Charges"
                    ];
                    break;

            case "venteMandat":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille(["Carburant mandat", "Lavage mandat", "Mandat Autres"], "poste"),
                    "typeCommentaires" => "Produits"
                ];
                break;

            case "venteMarchandises":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille(["Pétroliers", "Boutiques", "Bar Buffet", "Automobile", "Carburants"], "poste"),
                    "typeCommentaires" => "Produits"
                ];
                break;

            case "commissionsHorsMandat":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille(["Commissions hors mandat", "Ex. Antérieurs"], "poste"),
                    "typeCommentaires" => "Produits"
                ];
                break;

            case "lavageHorsMandat":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Lavage Hors Mandat", "poste"),
                    "typeCommentaires" => "Produits"
                ];
                break;

            case "autresProduits":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Autres produits hors mandat", "poste"),
                    "typeCommentaires" => "Produits"
                ];
                break;

            case "autresPrestationsServices":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Opération de service", "poste"),
                    "typeCommentaires" => "Produits"
                ];
                break;

            case "stocks":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Stock", "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;

            case "comptesDeRegularisation":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Comptes de régularisation", "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;

            case "provisionsRisquesCharges":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Provisions risques et charges", "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;

            case "immobilisations":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille(["Immobilisations corporelles", "Immobilisations financières", "Immobilisations Incorporelles"], "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;
            
            case "capitauxPropres":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Capitaux propres", "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;

            case "creances":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Créances", "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;

            case "disponibilites":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Disponibilités", "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;

            case "chargesConstatesAvance":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Charges constatées d'avance", "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;

            case "cca":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille("Compte courant d'associés", "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;

            case "dettes":
                $return = [
                    "commentaires" => $this->getCommentaireByFamille(["Dettes à plus d'un an", "Dettes à moins d'un an", "Fournisseurs TRD", "Autres dettes", "Autres dettes et charges à payer"], "bilan"),
                    "typeCommentaires" => "Bilan"
                ];
                break;

            default:
                break;
        }

        return $return;
    }

    public function getResultatCommentaire(): array
    {
        $resultat = $this->bilanRepository->getResultat($_SESSION["station_DOS_NUM"], $_SESSION["MoisHisto"])["BALI_RES"];

        $sens = $resultat < 0 ? "déficitaire" : "bénéficiaire";

        return ["La soci&eacute;t&eacute; dégage un résultat $sens de " . number_format($resultat, 2, ',', ' ') . " €."];

    }

    public function getEcartReelPrevuCommentaire(): array
    {
        $resultat = $this->balanceService->getEcartReelPrevuFormatAmeliorationDeterioration();

        $tableauAmelioration = [
            "titre" => "<span style='text-decoration: 4px green underline !important; text-underline-offset: 4px !important;'>PRINCIPAUX ÉLEMENTS AMÉLIORANT LE RÉSULTAT</span>",
            "entete" => ["", "Montant prévu", "Montant réel", "Ecart"]
        ];

        $tableauDeterioration = [
            "titre" => "<span style='text-decoration: 4px red underline !important; text-underline-offset: 4px !important;'>PRINCIPAUX ÉLEMENTS DÉTÉRIORANT LE RÉSULTAT</span>",
            "entete" => ["", "Montant prévu", "Montant réel", "Ecart"]
        ];

        foreach ($resultat["amelioration"] as $famille => $montant) {
            $tableauAmelioration["lignes"][] = [
                $famille,
                number_format($montant["Prevu"], 0, ',', ' '),
                number_format($montant["Realise"], 0, ',', ' '),
                '<span class="darkgreen">' . number_format($montant["Ecart"], 0, ',', ' ') . '</span>'
            ];
        }

        foreach ($resultat["deterioration"] as $famille => $montant) {
            $tableauDeterioration["lignes"][] = [
                $famille,
                number_format($montant["Prevu"], 0, ',', ' '),
                number_format($montant["Realise"], 0, ',', ' '),
                '<span class="red">' . number_format($montant["Ecart"], 0, ',', ' ') . '</span>'
            ];
        }

        return [$tableauAmelioration, $tableauDeterioration];
    }

    public function getEcartReelPrevuMasseSalarialeCommentaire($codePostes): array
    {
        $resultats = $this->balanceRepository->getEcartReelPrevu(array_keys($codePostes));

        $tableau = [
            "titre" => "",
            "entete" => ["", "Prévu", "Réel", "Écart", "Écart (%)"]
        ];

        $postes = [];

        foreach ($resultats as $resultat) {
            $postes[$resultat["codePoste"]][$resultat["realise"]] = $resultat;
        }

        foreach ($postes as $codePoste => $value) {
            $tableau["lignes"][] = [
                utf8_decode($codePostes[$codePoste]),
                number_format($value["prevu"]["Montant"], 0, ',', ' '),
                number_format($value["realise"]["Montant"], 0, ',', ' '),
                number_format($value["prevu"]["Montant"] - $value["realise"]["Montant"], 0, ',', ' '),
                number_format(($value["prevu"]["Montant"] - $value["realise"]["Montant"]) / $value["prevu"]["Montant"] * 100, 0, ',', ' ') . " %"
            ];
        }

        return [$tableau];
    }

    public function getCommentaireByFamille($slugOrId, $type, $bySlug = true): array
    {
        if ($bySlug) {
            $commentaires = $this->getDetailCommentaireByPosteBySlug($slugOrId, $type);
        } else {
            $commentaires = $this->getDetailCommentaireByPoste($slugOrId, $type);
        }

        $mesCommentaires = [];

        foreach ($commentaires as $key => $commentaire) {
            if (!empty($commentaire["comptes"])) {
                foreach ($commentaire["comptes"] as $codeCompte => $compte) {
                    if (isset($compte["commentaire"]) && $compte["commentaire"]) {
                        $mesCommentaires[$key]["comptes"][$codeCompte] = $compte;
                    }
                }
            }

            if ((isset($commentaire["poste"]["commentaire"]) && $commentaire["poste"]["commentaire"]) || !empty($mesCommentaires[$key]["comptes"])) {
                $mesCommentaires[$key]["poste"] = $commentaire["poste"];
            }
        }

        return $mesCommentaires;
    }
}
