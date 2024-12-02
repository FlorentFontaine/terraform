<?php

use Helpers\Encoding;
use Helpers\StringHelper;

/**
 * Class HTMLElement
 *
 * Represents an HTML element.
 */
class HTMLElement
{
    public string $BaliseName;

    // Tag pour la balise
    // ["style" => "text:align:right", "class" => "maclass"]
    public array $Tags = [];

    public $Value;
    public bool $Totalise;
    public bool $InTotal;

    public array $Childrens;

    public int $GroupeCpt;

    public function __construct($BaliseName, $Value = null)
    {
        $this->BaliseName = $BaliseName;
        $this->Value = $Value;
        $this->GroupeCpt = 0;
        $this->Childrens = [];
        $this->Totalise = false;
        $this->InTotal = true;
    }

    /**
     * Adds a total element to the container.
     *
     * This method creates a total element using the provided name, and adds it to the container.
     * If the $ReturnSeulementVal parameter is set to false (default), the total element will also be added as a child
     * element to the container. Otherwise, it will only be created and returned.
     *
     * @param string $Name The name of the total element.
     * @param bool $ReturnSeulementVal (optional) Whether to only return the total element without adding it as a child.
     *                                 Defaults to false.
     * @return HTMLElement The total element.
     */
    public function add_Total(string $Name, bool $ReturnSeulementVal = false): HTMLElement
    {
        $LnTotal = Totaliseur_groupe::Totalise($this, $this->GroupeCpt, 1, $Name);

        if (!$ReturnSeulementVal) {
            $this->add_Children($LnTotal);
        }

        $this->GroupeCpt++;

        return $LnTotal;
    }

    /**
     * Adds a sub-total to the current totalizer group.
     *
     * This method invokes the Totalise() method from the Totaliseur_groupe class and adds the result as a child to the current totalizer group.
     *
     * @param string $Name The name of the sub-total.
     * @param bool $All Optional. Determines whether all values or only the ones that satisfy certain conditions should be included in the sub-total calculation. Defaults to false.
     * @return void
     */
    public function add_STotal(string $Name, bool $All = false)
    {
        $this->add_Children(Totaliseur_groupe::Totalise($this, $this->GroupeCpt, 2, $Name, $All));
    }

    /**
     * Adds a child element to the current HTML element.
     *
     * This method adds the specified child element to the current HTML element's children array using the
     * current group counter as the key.
     *
     * @param HTMLElement $Children The child element to be added.
     *
     * @return void
     */
    public function add_Children(HTMLElement $Children)
    {
        $Children->GroupeCpt = $this->GroupeCpt;
        $this->Childrens[$this->GroupeCpt][] = &$Children;
    }

    /**
     * Retrieves the HTML representation of the object as a string.
     *
     * This method concatenates the start balise, children nodes (if any), and end balise to form the XML representation.
     *
     * @return string The XML representation of the object.
     */
    public function toString(): string
    {
        $Return = $this->get_StartBalise();

        if ($this->Childrens) {
            foreach ($this->Childrens as $ArrayChildren) {
                foreach ($ArrayChildren as $UnChildren) {
                    $Return .= $UnChildren->toString();
                }
            }
        } else {
            if (is_numeric($this->Value)) {
                $this->Value = StringHelper::NombreFr($this->Value);
            }

            $Return .= Encoding::toUTF8($this->Value);
        }

        $Return .= $this->get_EndBalise();

        return $Return;
    }

    /**
     * Retrieves the start balise as a string.
     *
     * This method concatenates the balise name and the tag string to form the start balise.
     *
     * @return string The start balise.
     */
    private function get_StartBalise(): string
    {
        return "\n<" . $this->BaliseName . " " . $this->get_TagString() . ">";
    }

    /**
     * Returns the closing tag string for the current element.
     *
     * This method constructs a string representation of the closing tag for the current element.
     * The closing tag is created by concatenating the closing angle bracket, the element's tag name,
     * and the opening angle bracket.
     *
     * @return string The closing tag string for the current element.
     */
    private function get_EndBalise(): string
    {
        return "</" . $this->BaliseName . ">";
    }

    /**
     * Returns a string representation of the tags.
     *
     * This method iterates over the tags and constructs a string representation in the form of attribute-value pairs.
     * Each tag is formatted as "attribute=\"value\"" and separated by a space.
     *
     * @return string The string representation of the tags.
     */
    private function get_TagString(): string
    {
        $Return = "";

        foreach ($this->Tags as $UneCleTag => $UneValTag) {
            $Return .= " $UneCleTag=\"$UneValTag\" ";
        }

        return $Return;
    }
}

/**
 * Class Totaliseur_groupe
 *
 * Represents a group totalizer for HTML elements.
 */
class Totaliseur_groupe
{
    /**
     * Totalise method calculates the total values of a given HTML element.
     *
     * @param $UnHTMLElement - The HTML element to be totalized.
     * @param $Groupe - The group identifier of the elements to be totalized.
     * @param $Type - The type of totalization. 1 represents subtotal, 2 represents grand total.
     * @param string $Name - The name of the total row (optional).
     * @param bool $TotaliseAll - Flag to indicate whether to totalize all elements (default: false).
     *
     * @return HTMLElement - The total row element with calculated values.
     */
    public static function Totalise($UnHTMLElement, $Groupe, $Type, string $Name = '', bool $TotaliseAll = false): HTMLElement
    {
        $Vals = [];

        //parcours des tr du groupe voulu
        foreach ($UnHTMLElement->Childrens[$Groupe] as $UnChild) {
            //si ce n'est pas une ligne de total
            if (!key_exists("tot", $UnChild->Tags)) {
                if ($UnChild->Childrens) {
                    if (($Type == 2 && !$TotaliseAll && $UnChild->Totalise) || !$UnChild->InTotal) {
                        //ne pas prendre la ligne dans le total
                    } else {
                        //parcours des groupes de td
                        foreach ($UnChild->Childrens as $UnChild2) {//parcours des Tds
                            foreach ($UnChild2 as $positionTd => $UnTd) {
                                $MonMontant = StringHelper::Texte2Nombre($UnTd->Value);

                                if (!isset($Vals[$positionTd])) {
                                    $Vals[$positionTd] = 0;
                                }

                                $Vals[$positionTd] += $MonMontant;
                            }
                        }
                    }
                } else {
                    if (($Type == 2 && !$TotaliseAll && $UnChild->Totalise) || !$UnChild->InTotal) {
                        //ne pas prendre la ligne dans le total
                    } else {
                        $MonMontant = StringHelper::Texte2Nombre($UnChild->Value);

                        if (is_numeric($MonMontant)) {
                            $Vals += $MonMontant;
                        }
                    }
                }

                $UnChild->Totalise = true;    //on dit que cette ligne a déja été totalisé
            }
        }

        if (is_array($Vals)) {
            $TRTotal = new HTMLElement("tr");
            $Prem = true;

            foreach ($Vals as $UneValeurTotal) {
                if ($Prem && $Name) {
                    //si on donne un nom, on le met sur le premier td
                    $TdName = new HTMLElement("td", $Name);

                    if ($Type == 2) {
                        $TRTotal->Tags["class"] = "lnstotal";
                    } else {
                        $TRTotal->Tags["class"] = "lntotal";
                    }

                    $TdName->Tags["align"] = "right";
                    $TRTotal->add_Children($TdName);
                } else {
                    $TdValeur = new HTMLElement("td", $UneValeurTotal);
                    $TdValeur->Tags["align"] = "right";
                    $TRTotal->add_Children($TdValeur);
                }

                $Prem = false;
            }
        } else {
            $TRTotal = new HTMLElement("td", $Vals);//StringHelper::NombreFr
        }

        $TRTotal->Tags["tot"] = "'$Type'";
        return $TRTotal;
    }
}
