<?php



/***************************************************************
 *  Copyright notice
 *  
 *  (c) 2008 Fabrizio Branca (branca@punkt.de)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is 
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 * 
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 * 
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/*******************************************************************************
 * Script :  PDF_Code128
 * Version : 1.0
 * Date :    20/05/2008
 * Auteur :  Roland Gautier
 *
 * C128($x,$y,$code,$w,$h)
 *     $x,$y :     angle supérieur gauche du code à barre
 *     $code :     le code à créer
 *     $w :        largeur hors tout du code dans l'unité courante
 *                 (prévoir 5 à 15 mm de blanc à droite et à gauche)
 *     $h :        hauteur hors tout du code dans l'unité courante
 *
 * Commutation des jeux ABC automatique et optimisée.
 *******************************************************************************/

class tx_ptxml2pdf_barcode_code128 {
	
	public $T128; // tableau des codes 128
	public $ABCset = ""; // jeu des caractères éligibles au C128
	public $Aset = ""; // Set A du jeu des caractères éligibles
	public $Bset = ""; // Set B du jeu des caractères éligibles
	public $Cset = ""; // Set C du jeu des caractères éligibles
	public $SetFrom; // Convertisseur source des jeux vers le tableau
	public $SetTo; // Convertisseur destination des jeux vers le tableau
	public $JStart = array("A" => 103, "B" => 104, "C" => 105); // Caractères de sélection de jeu au début du C128
	public $JSwap = array("A" => 101, "B" => 100, "C" => 99); // Caractères de changement de jeu


	/**
	 * Constructor
	 * 
	 * Properties are filled with data
	 */
	public function __construct() {
		
		$this->T128[] = array(2, 1, 2, 2, 2, 2); //0 : [ ]               // composition des caractères
		$this->T128[] = array(2, 2, 2, 1, 2, 2); //1 : [!]
		$this->T128[] = array(2, 2, 2, 2, 2, 1); //2 : ["]
		$this->T128[] = array(1, 2, 1, 2, 2, 3); //3 : [#]
		$this->T128[] = array(1, 2, 1, 3, 2, 2); //4 : [$]
		$this->T128[] = array(1, 3, 1, 2, 2, 2); //5 : [%]
		$this->T128[] = array(1, 2, 2, 2, 1, 3); //6 : [&]
		$this->T128[] = array(1, 2, 2, 3, 1, 2); //7 : [']
		$this->T128[] = array(1, 3, 2, 2, 1, 2); //8 : [(]
		$this->T128[] = array(2, 2, 1, 2, 1, 3); //9 : [)]
		$this->T128[] = array(2, 2, 1, 3, 1, 2); //10 : [*]
		$this->T128[] = array(2, 3, 1, 2, 1, 2); //11 : [+]
		$this->T128[] = array(1, 1, 2, 2, 3, 2); //12 : [,]
		$this->T128[] = array(1, 2, 2, 1, 3, 2); //13 : [-]
		$this->T128[] = array(1, 2, 2, 2, 3, 1); //14 : [.]
		$this->T128[] = array(1, 1, 3, 2, 2, 2); //15 : [/]
		$this->T128[] = array(1, 2, 3, 1, 2, 2); //16 : [0]
		$this->T128[] = array(1, 2, 3, 2, 2, 1); //17 : [1]
		$this->T128[] = array(2, 2, 3, 2, 1, 1); //18 : [2]
		$this->T128[] = array(2, 2, 1, 1, 3, 2); //19 : [3]
		$this->T128[] = array(2, 2, 1, 2, 3, 1); //20 : [4]
		$this->T128[] = array(2, 1, 3, 2, 1, 2); //21 : [5]
		$this->T128[] = array(2, 2, 3, 1, 1, 2); //22 : [6]
		$this->T128[] = array(3, 1, 2, 1, 3, 1); //23 : [7]
		$this->T128[] = array(3, 1, 1, 2, 2, 2); //24 : [8]
		$this->T128[] = array(3, 2, 1, 1, 2, 2); //25 : [9]
		$this->T128[] = array(3, 2, 1, 2, 2, 1); //26 : [:]
		$this->T128[] = array(3, 1, 2, 2, 1, 2); //27 : [;]
		$this->T128[] = array(3, 2, 2, 1, 1, 2); //28 : [<]
		$this->T128[] = array(3, 2, 2, 2, 1, 1); //29 : [=]
		$this->T128[] = array(2, 1, 2, 1, 2, 3); //30 : [>]
		$this->T128[] = array(2, 1, 2, 3, 2, 1); //31 : [?]
		$this->T128[] = array(2, 3, 2, 1, 2, 1); //32 : [@]
		$this->T128[] = array(1, 1, 1, 3, 2, 3); //33 : [A]
		$this->T128[] = array(1, 3, 1, 1, 2, 3); //34 : [B]
		$this->T128[] = array(1, 3, 1, 3, 2, 1); //35 : [C]
		$this->T128[] = array(1, 1, 2, 3, 1, 3); //36 : [D]
		$this->T128[] = array(1, 3, 2, 1, 1, 3); //37 : [E]
		$this->T128[] = array(1, 3, 2, 3, 1, 1); //38 : [F]
		$this->T128[] = array(2, 1, 1, 3, 1, 3); //39 : [G]
		$this->T128[] = array(2, 3, 1, 1, 1, 3); //40 : [H]
		$this->T128[] = array(2, 3, 1, 3, 1, 1); //41 : [I]
		$this->T128[] = array(1, 1, 2, 1, 3, 3); //42 : [J]
		$this->T128[] = array(1, 1, 2, 3, 3, 1); //43 : [K]
		$this->T128[] = array(1, 3, 2, 1, 3, 1); //44 : [L]
		$this->T128[] = array(1, 1, 3, 1, 2, 3); //45 : [M]
		$this->T128[] = array(1, 1, 3, 3, 2, 1); //46 : [N]
		$this->T128[] = array(1, 3, 3, 1, 2, 1); //47 : [O]
		$this->T128[] = array(3, 1, 3, 1, 2, 1); //48 : [P]
		$this->T128[] = array(2, 1, 1, 3, 3, 1); //49 : [Q]
		$this->T128[] = array(2, 3, 1, 1, 3, 1); //50 : [R]
		$this->T128[] = array(2, 1, 3, 1, 1, 3); //51 : [S]
		$this->T128[] = array(2, 1, 3, 3, 1, 1); //52 : [T]
		$this->T128[] = array(2, 1, 3, 1, 3, 1); //53 : [U]
		$this->T128[] = array(3, 1, 1, 1, 2, 3); //54 : [V]
		$this->T128[] = array(3, 1, 1, 3, 2, 1); //55 : [W]
		$this->T128[] = array(3, 3, 1, 1, 2, 1); //56 : [X]
		$this->T128[] = array(3, 1, 2, 1, 1, 3); //57 : [Y]
		$this->T128[] = array(3, 1, 2, 3, 1, 1); //58 : [Z]
		$this->T128[] = array(3, 3, 2, 1, 1, 1); //59 : [[]
		$this->T128[] = array(3, 1, 4, 1, 1, 1); //60 : [\]
		$this->T128[] = array(2, 2, 1, 4, 1, 1); //61 : []]
		$this->T128[] = array(4, 3, 1, 1, 1, 1); //62 : [^]
		$this->T128[] = array(1, 1, 1, 2, 2, 4); //63 : [_]
		$this->T128[] = array(1, 1, 1, 4, 2, 2); //64 : [`]
		$this->T128[] = array(1, 2, 1, 1, 2, 4); //65 : [a]
		$this->T128[] = array(1, 2, 1, 4, 2, 1); //66 : [b]
		$this->T128[] = array(1, 4, 1, 1, 2, 2); //67 : [c]
		$this->T128[] = array(1, 4, 1, 2, 2, 1); //68 : [d]
		$this->T128[] = array(1, 1, 2, 2, 1, 4); //69 : [e]
		$this->T128[] = array(1, 1, 2, 4, 1, 2); //70 : [f]
		$this->T128[] = array(1, 2, 2, 1, 1, 4); //71 : [g]
		$this->T128[] = array(1, 2, 2, 4, 1, 1); //72 : [h]
		$this->T128[] = array(1, 4, 2, 1, 1, 2); //73 : [i]
		$this->T128[] = array(1, 4, 2, 2, 1, 1); //74 : [j]
		$this->T128[] = array(2, 4, 1, 2, 1, 1); //75 : [k]
		$this->T128[] = array(2, 2, 1, 1, 1, 4); //76 : [l]
		$this->T128[] = array(4, 1, 3, 1, 1, 1); //77 : [m]
		$this->T128[] = array(2, 4, 1, 1, 1, 2); //78 : [n]
		$this->T128[] = array(1, 3, 4, 1, 1, 1); //79 : [o]
		$this->T128[] = array(1, 1, 1, 2, 4, 2); //80 : [p]
		$this->T128[] = array(1, 2, 1, 1, 4, 2); //81 : [q]
		$this->T128[] = array(1, 2, 1, 2, 4, 1); //82 : [r]
		$this->T128[] = array(1, 1, 4, 2, 1, 2); //83 : [s]
		$this->T128[] = array(1, 2, 4, 1, 1, 2); //84 : [t]
		$this->T128[] = array(1, 2, 4, 2, 1, 1); //85 : [u]
		$this->T128[] = array(4, 1, 1, 2, 1, 2); //86 : [v]
		$this->T128[] = array(4, 2, 1, 1, 1, 2); //87 : [w]
		$this->T128[] = array(4, 2, 1, 2, 1, 1); //88 : [x]
		$this->T128[] = array(2, 1, 2, 1, 4, 1); //89 : [y]
		$this->T128[] = array(2, 1, 4, 1, 2, 1); //90 : [z]
		$this->T128[] = array(4, 1, 2, 1, 2, 1); //91 : [{]
		$this->T128[] = array(1, 1, 1, 1, 4, 3); //92 : [|]
		$this->T128[] = array(1, 1, 1, 3, 4, 1); //93 : [}]
		$this->T128[] = array(1, 3, 1, 1, 4, 1); //94 : [~]
		$this->T128[] = array(1, 1, 4, 1, 1, 3); //95 : [DEL]
		$this->T128[] = array(1, 1, 4, 3, 1, 1); //96 : [FNC3]
		$this->T128[] = array(4, 1, 1, 1, 1, 3); //97 : [FNC2]
		$this->T128[] = array(4, 1, 1, 3, 1, 1); //98 : [SHIFT]
		$this->T128[] = array(1, 1, 3, 1, 4, 1); //99 : [Cswap]
		$this->T128[] = array(1, 1, 4, 1, 3, 1); //100 : [Bswap]                
		$this->T128[] = array(3, 1, 1, 1, 4, 1); //101 : [Aswap]
		$this->T128[] = array(4, 1, 1, 1, 3, 1); //102 : [FNC1]
		$this->T128[] = array(2, 1, 1, 4, 1, 2); //103 : [Astart]
		$this->T128[] = array(2, 1, 1, 2, 1, 4); //104 : [Bstart]
		$this->T128[] = array(2, 1, 1, 2, 3, 2); //105 : [Cstart]
		$this->T128[] = array(2, 3, 3, 1, 1, 1); //106 : [STOP]
		$this->T128[] = array(2, 1); //107 : [END BAR]
		

		for($i = 32; $i <= 95; $i++) { // jeux de caractères
			$this->ABCset .= chr($i);
		}
		$this->Aset = $this->ABCset;
		$this->Bset = $this->ABCset;
		for($i = 0; $i <= 31; $i++) {
			$this->ABCset .= chr($i);
			$this->Aset .= chr($i);
		}
		for($i = 96; $i <= 126; $i++) {
			$this->ABCset .= chr($i);
			$this->Bset .= chr($i);
		}
		$this->Cset = "0123456789";
		
		for($i = 0; $i < 96; $i++) { // convertisseurs des jeux A & B  
			@$this->SetFrom["A"] .= chr($i);
			@$this->SetFrom["B"] .= chr($i + 32);
			@$this->SetTo["A"] .= chr(($i < 32) ? $i + 64 : $i - 32);
			@$this->SetTo["B"] .= chr($i);
		}
	}

}

?>