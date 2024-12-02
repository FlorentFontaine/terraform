<?php

namespace App\htmlClasses;

use Classes\DB\QueryBuilder;
use Helpers\URLHelper;

class TableV2
{

    private QueryBuilder $query;

    private array $get;

    private int $limit = 50;

    private array $sortable = [];

    private array $columns = [];

    private array $formatters = [];

    private bool $pagination;

    private array $line = [];

    private string $title = "";

    private string $classes = "";

    private array $styles = [];

    const SORT_KEY = "sort";

    const DIR_KEY = "dir";

    public function __construct(QueryBuilder $query, array $get, $pagination = false)
    {
        $this->query = $query;
        $this->get = $get;
        $this->pagination = $pagination;
    }

    /**
     * Ajoute un formatage pour une colonne
     *
     * @param string $key
     * @param callable $function
     * @return $this
     */
    public function format(string $key, callable $function): self
    {
        $this->formatters[$key] = $function;

        return $this;
    }

    /**
     * Définit les colonnes qui sont triables
     *
     * @param string ...$sortable
     * @return $this
     */
    public function sortable(string ...$sortable): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Définit les colonnes du tableau
     *
     * @param array $columns
     * @return $this
     */
    public function columns(array $columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Définie les classes à donner à l'élément <TABLE>
     *
     * @param string $classes
     * @return TableV2
     */
    public function setClasses(string $classes): self
    {
        $this->classes = $classes;

        return $this;
    }

    public function setStyle(array $styles): self
    {
        foreach ($styles as $field => $elements) {
            $this->styles[$field] = $elements;
        }

        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = utf8_encode($title);

        return $this;
    }

    /**
     * Affiche le tableau généré
     *
     * @return string
     */
    public function render(): string
    {
        $this->addOrderToQuery();

        // Récupération des résultats
        $items = $this->query->getAll();

        $return = $this->displayTableau($items);

        if ($this->pagination) {
            $return .= $this->displayPagination($this->setPagination());
        }

        return $return;
    }

    /**
     * Retourne la clé de la première colonne spécifiée
     *
     * @return string
     */
    private function getFirstColumn(): string
    {
        return array_key_first($this->columns);
    }

    /**
     * Retourne les classes à donner à l'élément <TABLE>
     *
     * @return string
     */
    private function getClasses(): string
    {
        return $this->classes;
    }

    /**
     * Retourne les styles à appliquer pour un champ donné
     *
     * @param string $key
     * @return string
     */
    private function getStyles(string $key): string
    {
        $styles = "";

        if (isset($this->styles[$key])) {
            foreach ($this->styles[$key] as $property => $value) {
                $styles .= $property . ": " . $value . "; ";
            }
        }

        return $styles;
    }

    /**
     * Génère un <TH>
     *
     * @param string $key
     * @return string
     */
    private function th(string $key): string
    {
        if (!in_array($key, $this->sortable)) {
            return utf8_encode($this->columns[$key]);
        }

        $sort = $this->get[self::SORT_KEY] ?? null;
        $direction = $this->get[self::DIR_KEY] ?? null;

        // TODO En attente de trouver une icône pour le tri
        $icon = "";
//        if ($sort === $key) {
//            $icon = $direction === 'asc' ? "^" : "v";
//        }

        $url = URLHelper::WithParams($this->get, [
            self::SORT_KEY => $key,
            self::DIR_KEY => $direction === 'asc' && $sort === $key ? 'desc' : 'asc'
        ]);

        return '<a href="?' . $url . '">' . utf8_encode($this->columns[$key]) . ' ' . $icon . '</a>';
    }

    /**
     * Génère un <TD>
     *
     * @param string $key
     * @param array $item
     * @return mixed
     */
    private function td(string $key, array $item)
    {
        if (isset($this->formatters[$key])) {
            return $this->formatters[$key]($item[$key], $this->line);
        }

        return $item[$key];
    }

    /**
     * Ajoute à la requête les clause ORDER BY pour le tri sur le tableau
     *
     * @return void
     */
    private function addOrderToQuery()
    {
        // Si un tri est spécifié, on altère la requête
        if (!empty($this->get['sort']) && in_array($this->get['sort'], $this->sortable)) {
            $this->query->orderBy($this->get['sort'], $this->get['dir'] ?? 'ASC', true);
        } // Sinon, on donne la première colonne dans le sens ascendant par défaut
        else {
            $this->query->orderBy($this->getFirstColumn(), true);
        }
    }

    /**
     * Définie la pagination à la requête
     *
     * @return array
     */
    private function setPagination(): array
    {
        $page = $this->get['p'] ?? 1;
        $count = $this->query->count();

        // Calcul du nombre de pages nécessaire pour la requête
        $pages = ceil($count / $this->limit);

        // Application de la pagination, en fonction du numéro de la page
        $this->query->limit($this->limit)->page($page);

        return [$page, $pages];
    }

    /**
     * Affiche la navigation pour la pagination
     *
     * @param array $params
     * @return string
     */
    private function displayPagination(array $params): string
    {
        $return = "";

        if ($params['pages'] > 1 && $params['page'] > 1) {
            $return .= '<a href="?' . URLHelper::WithParam($this->get, "p", $params['page'] - 1) . '">Page précédente</a>';
        }

        if ($params['pages'] > 1 && $params['page'] < $params['pages']) {
            $return .= '<a href = "?' . URLHelper::WithParam($this->get, "p", $params['page'] + 1) . '" > Page suivante </a >';
        }

        return $return;
    }

    /**
     * Retourne le rendu HTML du tableau
     *
     * @param array $items
     * @return string
     */
    private function displayTableau(array $items): string
    {
        $return = '<table class="' . $this->getClasses() . '"><thead>';

        if ($this->title !== "") {
            $return .= '<tr><td class="TitreTable" colspan="' . count($this->columns) . '">' . $this->title . '</td></tr>';
        }

        $return .= '<tr>';

        // Définition des entêtes du tableau
        foreach ($this->columns as $key => $column) {
            $return .= '<th class="EnteteTab" style="' . $this->getStyles($key) . '">' . $this->th($key) . '</th>';
        }

        $return .= '</tr></thead><tbody>';

        // Itération sur chaque élément à afficher dans le tableau
        foreach ($items as $item) {
            if (isset($cssligne) && $cssligne == 'bdligneimpaireTD') {
                $cssligne = 'bdlignepaireTD';
            } else {
                $cssligne = 'bdligneimpaireTD';
            }

            $this->line = $item;
            $return .= '<tr class="' . $cssligne . '">';

            // Définition de chaque cellule du tableau
            foreach ($this->columns as $key => $column) {
                $return .= '<td style="' . $this->getStyles($key) . '">' . utf8_encode($this->td($key, $item)) . '</td>';
            }

            $return .= '</tr>';
        }

        if (empty($items)) {
            $return .= '<tr><td colspan="' . count($this->columns) . '" style="font-weight: bold; text-align: center">
                            Aucun r&eacute;sultat
                        </td></tr>';
        }

        $return .= '</tbody></table>';

        return $return;
    }
}
